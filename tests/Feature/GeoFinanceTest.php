<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\School;
use Tests\TestCase;

class GeoFinanceTest extends TestCase
{
    public function test_geofence_rejects_far_delivery_confirmation(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $driver = $this->makeUser($org, 'driver', 'geo@api.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-GF', 'name' => 'SD GF', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE', 'latitude' => -6.1754, 'longitude' => 106.8272, 'geofence_radius_m' => 300]);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-GF-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'IN_TRANSIT']);
        $token = $driver->createToken('t')->plainTextToken;

        // ~2.2km dari sekolah → ditolak untuk status ARRIVED.
        $this->withToken($token)->postJson("/api/v1/deliveries/{$delivery->id}/track", [
            'status' => 'ARRIVED', 'latitude' => -6.1954, 'longitude' => 106.8239,
        ])->assertStatus(422);

        // Di lokasi → diterima + actual_arrival tercatat.
        $this->withToken($token)->postJson("/api/v1/deliveries/{$delivery->id}/track", [
            'status' => 'ARRIVED', 'latitude' => -6.1755, 'longitude' => 106.8273,
        ])->assertCreated()->assertJsonPath('geofence', 'INSIDE');
        $this->assertNotNull($delivery->fresh()->actual_arrival);
    }

    public function test_route_optimize_reorders_stops(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $ck->update(['latitude' => 0, 'longitude' => 0]);
        $admin = $this->makeUser($org, 'admin', 'opt@mbg.id');
        $route = DeliveryRoute::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'RTE-OPT', 'name' => 'Opt', 'is_active' => true]);
        // Input sengaja terbalik: jauh dulu.
        $far = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-FAR', 'name' => 'Jauh', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE', 'latitude' => 0, 'longitude' => 10]);
        $near = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-NEAR', 'name' => 'Dekat', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE', 'latitude' => 0, 'longitude' => 1]);
        $route->stops()->create(['school_id' => $far->id, 'sequence' => 1]);
        $route->stops()->create(['school_id' => $near->id, 'sequence' => 2]);

        $this->actingAs($admin)->post("/tms/routes/{$route->id}/optimize")->assertRedirect();
        $seqs = $route->stops()->orderBy('sequence')->pluck('school_id')->toArray();
        $this->assertEquals([$near->id, $far->id], $seqs);
    }
}
