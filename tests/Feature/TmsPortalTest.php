<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\School;
use App\Models\SchoolConfirmation;
use App\Models\Vehicle;
use Tests\TestCase;

class TmsPortalTest extends TestCase
{
    public function test_route_apply_sets_vehicle_eta_sequence(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $admin = $this->makeUser($org, 'admin', 'tms@mbg.id');
        $vehicle = Vehicle::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'plate_no' => 'B 999 TMS', 'name' => 'Box', 'status' => 'ACTIVE']);
        $route = DeliveryRoute::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'RTE-T1', 'name' => 'R1', 'vehicle_id' => $vehicle->id, 'is_active' => true]);
        $s1 = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-T1', 'name' => 'SD T1', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $s2 = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-T2', 'name' => 'SD T2', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $route->stops()->create(['school_id' => $s1->id, 'sequence' => 1]);
        $route->stops()->create(['school_id' => $s2->id, 'sequence' => 2]);
        $d1 = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $s1->id, 'number' => 'DLV-TMS-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'PLANNED']);
        $d2 = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $s2->id, 'number' => 'DLV-TMS-2', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'PLANNED']);

        $this->actingAs($admin)->post("/tms/routes/{$route->id}/apply")->assertRedirect();
        $this->assertEquals($vehicle->id, $d1->fresh()->vehicle_id);
        $this->assertEquals(1, $d1->fresh()->stop_sequence);
        $this->assertEquals(2, $d2->fresh()->stop_sequence);
        $this->assertNotNull($d1->fresh()->eta);

        $this->actingAs($admin)->get('/tms/tower')->assertOk()->assertSee('DLV-TMS-1');
    }

    public function test_late_detection(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-TL', 'name' => 'SD TL', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $d = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-TL-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'DELIVERED', 'dispatched_at' => now()->subHours(3), 'eta' => now()->subHours(2), 'actual_arrival' => now()->subHour()]);
        $this->assertTrue($d->isLate());
    }

    public function test_school_portal_confirmation_flow(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $schoolUser = $this->makeUser($org, 'school', 'school@mbg.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-P2', 'name' => 'SD P2', 'level' => 'SD', 'student_count' => 60, 'target_portions' => 60, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-P2-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 60, 'qty_delivered' => 60, 'status' => 'DELIVERED', 'delivered_at' => now()]);

        $this->actingAs($schoolUser)->get('/portal')->assertOk()->assertSee('DLV-P2-1');
        $this->actingAs($schoolUser)->post("/portal/{$delivery->id}/confirm", [
            'received_qty' => 58, 'rejected_qty' => 2, 'attendance' => 55,
            'complaint' => '2 box penyok', 'feedback' => 'Enak',
        ])->assertRedirect();
        $conf = SchoolConfirmation::where('delivery_id', $delivery->id)->firstOrFail();
        $this->assertEquals(58, $conf->received_qty);
        $this->actingAs($schoolUser)->get('/portal-complaints')->assertOk()->assertSee('2 box penyok');
    }

    public function test_school_user_cannot_see_other_org_delivery(): void
    {
        ['org' => $org, 'ck' => $ck] = $this->baseFixtures();
        $other = $this->makeOrg('ORGZ');
        $outsider = $this->makeUser($other, 'school', 'other@school.id');
        $school = School::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'code' => 'SCH-PX', 'name' => 'SD PX', 'level' => 'SD', 'student_count' => 10, 'target_portions' => 10, 'status' => 'ACTIVE']);
        $delivery = Delivery::create(['organization_id' => $org->id, 'central_kitchen_id' => $ck->id, 'school_id' => $school->id, 'number' => 'DLV-PX-1', 'delivery_date' => now()->toDateString(), 'qty_planned' => 10, 'status' => 'DELIVERED']);

        $this->actingAs($outsider)->get("/portal/{$delivery->id}")->assertForbidden();
    }
}
