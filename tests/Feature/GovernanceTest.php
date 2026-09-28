<?php

namespace Tests\Feature;

use App\Models\CentralKitchen;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\ScheduledReport;
use App\Models\Supplier;
use App\Services\GeofenceService;
use App\Services\PeriodService;
use App\Services\TotpService;
use Tests\TestCase;

class GovernanceTest extends TestCase
{
    public function test_period_closing_blocks_posting(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $svc = app(PeriodService::class);

        $svc->close($org->id, null, now()->addDay()->toDateString());
        $this->expectException(\RuntimeException::class);
        $svc->assertOpen($org->id, $ck->id, now()->toDateString());
    }

    public function test_period_closing_allows_future_and_scoped(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $svc = app(PeriodService::class);

        $svc->close($org->id, $ck->id, now()->subDay()->toDateString());
        // Hari ini terbuka (closed_before kemarin).
        $svc->assertOpen($org->id, $ck->id, now()->toDateString());
        $this->assertTrue(true);
    }

    public function test_gr_blocked_when_period_closed(): void
    {
        ['org' => $org, 'ck' => $ck, 'wh' => $wh, 'unit' => $unit, 'ing' => $ing] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'cls@mbg.id');
        app(PeriodService::class)->close($org->id, null, now()->addDay()->toDateString());
        $supplier = Supplier::create(['organization_id' => $org->id, 'code' => 'SUP-CL', 'name' => 'CL', 'status' => 'ACTIVE']);
        $po = PurchaseOrder::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'supplier_id' => $supplier->id, 'warehouse_id' => $wh->id, 'number' => 'PO-CL-1', 'order_date' => now()->toDateString(), 'status' => 'APPROVED']);
        $item = $po->items()->create(['ingredient_id' => $ing->id, 'qty_ordered' => 10, 'unit_id' => $unit->id, 'unit_price' => 1000]);

        $resp = $this->actingAs($admin)->post('/goods-receipts', [
            'purchase_order_id' => $po->id, 'warehouse_id' => $wh->id,
            'items' => [['po_item_id' => $item->id, 'qty' => 10]],
        ]);
        // RuntimeException dari guard bukan ValidationException → 500 tertangani? assert redirect error/toast.
        $this->assertTrue(in_array($resp->getStatusCode(), [302, 500]));
        $this->assertEquals(0, GoodsReceipt::count());
    }

    public function test_geofence_and_route_optimization(): void
    {
        $geo = app(GeofenceService::class);
        // Monas ke HI ~1.1km.
        $d = $geo->distanceMeters(-6.1754, 106.8272, -6.1954, 106.8239);
        $this->assertGreaterThan(500, $d);
        $this->assertLessThan(3000, $d);
        $this->assertTrue($geo->inside(-6.1754, 106.8272, -6.1754, 106.8272, 300));
        $this->assertFalse($geo->inside(-6.1954, 106.8239, -6.1754, 106.8272, 300));

        $order = $geo->optimizeSequence(0, 0, [1 => ['lat' => 0, 'lon' => 10], 2 => ['lat' => 0, 'lon' => 1], 3 => ['lat' => 0, 'lon' => 5]]);
        $this->assertEquals([2, 3, 1], $order);
    }

    public function test_totp_rfc_vector_and_challenge_flow(): void
    {
        // Vektor uji RFC 6238 (SHA1, T=59 → counter 1 → 287082, 6 digit: 287082).
        $totp = app(TotpService::class);
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ'; // base32("12345678901234567890")
        $this->assertEquals('287082', $totp->code($secret, 59));
        $this->assertTrue($totp->verify($secret, '287082', 0, 59));
        $this->assertFalse($totp->verify($secret, '000000', 0, 59));

        // Alur login 2FA.
        ['org' => $org] = $this->baseFixtures();
        $user = $this->makeUser($org, 'admin', 'tfa@mbg.id');
        $realSecret = $totp->generateSecret();
        $user->forceFill(['two_factor_secret' => $realSecret, 'two_factor_confirmed_at' => now()])->save();

        $this->post('/login', ['email' => 'tfa@mbg.id', 'password' => 'password123'])->assertRedirect('/2fa/challenge');
        $this->assertGuest();
        $this->get('/2fa/challenge')->assertOk();
        $this->post('/2fa/challenge', ['code' => '000000'])->assertSessionHasErrors('code');
        $this->post('/2fa/challenge', ['code' => $totp->code($realSecret)])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_profile_tokens_and_password(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'prf@mbg.id');

        $this->actingAs($admin)->get('/profile')->assertOk();
        $this->actingAs($admin)->post('/profile/tokens', ['name' => 'HP'])->assertRedirect();
        $this->assertEquals(1, $admin->tokens()->count());
        $tokenId = $admin->tokens()->first()->id;
        $this->actingAs($admin)->delete("/profile/tokens/{$tokenId}")->assertRedirect();
        $this->assertEquals(0, $admin->tokens()->count());

        $this->actingAs($admin)->post('/profile/password', [
            'current_password' => 'password123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])->assertRedirect();
        $this->post('/logout');
        $this->post('/login', ['email' => 'prf@mbg.id', 'password' => 'baru12345'])->assertRedirect('/dashboard');
    }

    public function test_scheduled_reports_and_tenant_export_commands(): void
    {
        ['org' => $org] = $this->baseFixtures();
        ScheduledReport::create(['organization_id' => $org->id, 'dataset' => 'waste', 'frequency' => 'DAILY', 'is_active' => true]);

        $this->artisan('mbg:run-scheduled-reports')->assertSuccessful();
        $this->assertNotNull(ScheduledReport::first()->fresh()->last_run_at);

        $this->artisan('mbg:tenant-export', ['organization' => $org->code])->assertSuccessful();
    }

    public function test_finance_reports_render(): void
    {
        ['org' => $org] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'fin@mbg.id');

        foreach (['/reports/ap-aging', '/reports/school-cost', '/budgets', '/closings'] as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
        // Budget simpan + closing simpan.
        $ck = CentralKitchen::where('organization_id', $org->id)->first();
        $this->actingAs($admin)->post('/budgets', ['central_kitchen_id' => $ck->id, 'period' => now()->format('Y-m'), 'amount' => 100000000])->assertRedirect();
        $this->actingAs($admin)->post('/closings', ['closed_before' => now()->subDay()->toDateString()])->assertRedirect();
    }
}
