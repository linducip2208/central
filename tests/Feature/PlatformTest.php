<?php

namespace Tests\Feature;

use App\Jobs\DeliverWebhook;
use App\Models\Approval;
use App\Models\Delivery;
use App\Models\PurchaseRequest;
use App\Models\School;
use App\Models\Unit;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\AiAdvisorInterface;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    public function test_approval_inbox_records_decisions(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'appr@mbg.id');
        $pr = PurchaseRequest::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'number' => 'PR-APR-1', 'request_date' => now()->toDateString(), 'status' => 'SUBMITTED', 'requested_by' => $admin->id]);
        $pr->items()->create(['ingredient_id' => $this->makeIngredient($org, $this->makeUnit(), 'ING-APR')->id, 'qty_requested' => 5, 'unit_id' => Unit::first()->id]);

        $this->actingAs($admin)->post("/purchase-requests/{$pr->id}/approve", ['approved' => [$pr->items()->first()->id => 5]])->assertRedirect();
        $approval = Approval::where('approvable_type', PurchaseRequest::class)->where('approvable_id', $pr->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('APPROVED', $approval->status);

        $this->actingAs($admin)->get('/approvals')->assertOk()->assertSee('PR-APR-1');
    }

    public function test_webhook_crud_and_signed_retry(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'wh@mbg.id');

        $this->actingAs($admin)->post('/webhooks', [
            'name' => 'ERP Hook', 'url' => 'https://example.test/hook', 'events' => ['goods.received', 'qc.failed'],
        ])->assertRedirect();
        $hook = Webhook::where('organization_id', $org->id)->firstOrFail();
        $this->assertNotEmpty($hook->secret);

        // Dispatch tanpa HTTP sungguhan: fake HTTP agar DELIVERED.
        Http::fake(['*' => Http::response([], 200)]);
        $n = app(WebhookService::class)->dispatch('goods.received', ['number' => 'GR-X'], $org->id);
        $this->assertEquals(1, $n);
        $delivery = WebhookDelivery::latest()->first();
        (new DeliverWebhook($delivery->id))->handle(app(WebhookService::class));
        $this->assertEquals('DELIVERED', $delivery->fresh()->status);

        // Signature format.
        $sig = app(WebhookService::class)->signature('secret', ['a' => 1]);
        $this->assertStringStartsWith('sha256=', $sig);

        $this->actingAs($admin)->get('/webhooks')->assertOk()->assertSee('ERP Hook');
    }

    public function test_login_logout_audited(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $this->makeUser($org, 'admin', 'audit@mbg.id');

        $this->post('/login', ['email' => 'audit@mbg.id', 'password' => 'password123'])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('audit_logs', ['action' => 'LOGIN']);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertDatabaseHas('audit_logs', ['action' => 'LOGOUT']);
    }

    public function test_analytics_and_exports(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'bi@mbg.id');

        $this->actingAs($admin)->get('/analytics')->assertOk()->assertSee('Executive');
        foreach (['movements', 'deliveries', 'production', 'waste', 'costing'] as $ds) {
            $resp = $this->actingAs($admin)->get("/analytics/export/{$ds}");
            $resp->assertOk();
            $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
        }
        $this->actingAs($admin)->get('/analytics/export/unknown')->assertNotFound();
    }

    public function test_api_extensions(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'api9@mbg.id');
        $token = $admin->createToken('t')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/schools')->assertOk();
        $this->withToken($token)->getJson('/api/v1/recipients')->assertOk();
        $this->withToken($token)->getJson('/api/v1/allergens')->assertOk();
        $this->withToken($token)->getJson('/api/v1/boms')->assertOk();
        $this->withToken($token)->getJson('/api/v1/production-orders')->assertOk();
        $this->withToken($token)->getJson('/api/v1/inspections')->assertOk();
        $this->withToken($token)->getJson('/api/v1/recalls')->assertOk();
        $this->withToken($token)->getJson('/api/v1/invoices')->assertOk();
        $this->withToken($token)->postJson('/api/v1/bom-explode', ['product_id' => 999999, 'qty' => 1])->assertOk();
    }

    public function test_api_gps_validation(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $driver = $this->makeUser($org, 'driver', 'gps@api.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-GPS', 'name' => 'SD GPS', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-GPS-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'PLANNED']);
        $token = $driver->createToken('t')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/deliveries/{$delivery->id}/track", [
            'status' => 'IN_TRANSIT', 'latitude' => 999, 'longitude' => 106.8,
        ])->assertStatus(422);
    }

    public function test_advisory_services_are_explainable(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'prd' => $prd] = $this->baseFixtures();
        $advisor = app(AiAdvisorInterface::class);

        $forecast = $advisor->demandForecast($prd->id, 7);
        $this->assertEquals('deterministic', $forecast->method);
        $this->assertNotEmpty($forecast->summary);

        $expiry = $advisor->expiryRisk($wh->id);
        $this->assertEquals('expiry_risk', $expiry->topic);

        $waste = $advisor->wasteAnalysis($ck->id);
        $this->assertEquals('waste_analysis', $waste->topic);
    }
}
