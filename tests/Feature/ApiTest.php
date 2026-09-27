<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\School;
use App\Services\InventoryService;
use Tests\TestCase;

class ApiTest extends TestCase
{
    public function test_token_login_and_me(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $this->makeUser($org, 'admin', 'api@mbg.id');

        $resp = $this->postJson('/api/v1/token', ['email' => 'api@mbg.id', 'password' => 'password123']);
        $resp->assertOk()->assertJsonStructure(['token', 'user']);
        $token = $resp->json('token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('email', 'api@mbg.id');
        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();
    }

    public function test_token_rejects_wrong_credentials(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $this->makeUser($org, 'admin', 'api2@mbg.id');

        $this->postJson('/api/v1/token', ['email' => 'api2@mbg.id', 'password' => 'salah'])->assertStatus(422);
    }

    public function test_unauthenticated_api_denied(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/stock/summary')->assertUnauthorized();
    }

    public function test_stock_endpoints(): void
    {
        ['org' => $org, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'api3@mbg.id');
        app(InventoryService::class)->receive([
            'organization_id' => $org->id, 'warehouse_id' => $wh->id,
            'item_type' => 'ingredient', 'item_id' => $ing->id,
            'qty' => 50, 'unit_id' => $unit->id, 'unit_cost' => 1000,
            'batch_no' => 'API-B1', 'reference_type' => 'T', 'reference_id' => 1,
        ]);
        $token = $admin->createToken('t')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/stock/summary')->assertOk()->assertJsonFragment(['item_id' => $ing->id]);
        $this->withToken($token)->getJson('/api/v1/stock/movements')->assertOk();
        $this->withToken($token)->getJson('/api/v1/stock/expiring?days=90')->assertOk();
    }

    public function test_driver_delivery_tracking_api(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $driver = $this->makeUser($org, 'driver', 'driver@api.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-API', 'name' => 'SD API', 'level' => 'SD', 'student_count' => 100, 'target_portions' => 100, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-API-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 100, 'status' => 'PLANNED', 'courier_id' => $driver->id]);
        $token = $driver->createToken('t')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/deliveries?mine=1')->assertOk()->assertJsonFragment(['number' => 'DLV-API-1']);
        $this->withToken($token)->postJson("/api/v1/deliveries/{$delivery->id}/track", [
            'status' => 'IN_TRANSIT', 'latitude' => -6.2, 'longitude' => 106.8, 'notes' => 'Berangkat',
        ])->assertCreated();
        $this->assertEquals('IN_TRANSIT', $delivery->fresh()->status);
    }

    public function test_cross_org_delivery_api_forbidden(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $other = $this->makeOrg('ORGY');
        $outsider = $this->makeUser($other, 'driver', 'out@api.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-APX', 'name' => 'SD APX', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-APX-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'PLANNED']);
        $token = $outsider->createToken('t')->plainTextToken;

        $this->withToken($token)->getJson("/api/v1/deliveries/{$delivery->id}")->assertForbidden();
    }
}
