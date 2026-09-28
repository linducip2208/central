<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('hold_reason', 100)->nullable()->after('status');
            $table->foreignId('bin_id')->nullable()->after('warehouse_id')->constrained('warehouse_bins')->nullOnDelete();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreignId('delivery_route_id')->nullable()->after('distribution_id')->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->after('delivery_route_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('stop_sequence')->default(0)->after('vehicle_id');
            $table->timestamp('eta')->nullable()->after('dispatched_at');
            $table->timestamp('actual_arrival')->nullable()->after('eta');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('invoice_status', 20)->default('UNBILLED')->after('status')->index();
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreignId('work_center_id')->nullable()->after('kitchen_unit_id')->constrained()->nullOnDelete();
            $table->decimal('theoretical_cost', 15, 2)->default(0)->after('notes');
            $table->string('material_status', 20)->default('PENDING')->after('theoretical_cost')->comment('PENDING,CHECKED,ISSUED');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->date('effective_from')->nullable()->after('version');
            $table->date('effective_to')->nullable()->after('effective_from');
            $table->unsignedInteger('prep_time_minutes')->default(0)->after('cook_time_minutes');
            $table->unsignedInteger('cooling_time_minutes')->default(0)->after('prep_time_minutes');
            $table->unsignedInteger('servings')->default(0)->after('cooling_time_minutes');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('reorder_point', 15, 3)->default(0)->after('min_stock');
            $table->decimal('safety_stock', 15, 3)->default(0)->after('reorder_point');
            $table->unsignedInteger('lead_time_days')->default(1)->after('safety_stock');
            $table->decimal('moq', 15, 3)->default(0)->after('lead_time_days');
            $table->foreignId('preferred_supplier_id')->nullable()->after('moq')->constrained('suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_supplier_id');
            $table->dropColumn(['reorder_point', 'safety_stock', 'lead_time_days', 'moq']);
        });
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['effective_from', 'effective_to', 'prep_time_minutes', 'cooling_time_minutes', 'servings']);
        });
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_center_id');
            $table->dropColumn(['theoretical_cost', 'material_status']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('invoice_status');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_route_id');
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn(['stop_sequence', 'eta', 'actual_arrival']);
        });
        Schema::table('batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bin_id');
            $table->dropColumn('hold_reason');
        });
    }
};
