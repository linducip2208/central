<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allergens', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('allergen_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['allergen_id', 'ingredient_id']);
        });

        Schema::create('allergen_recipient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allergen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->string('severity', 20)->default('AVOID');
            $table->timestamps();
            $table->unique(['allergen_id', 'recipient_id']);
        });

        Schema::create('meal_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->text('dietary_notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('meal_group_recipient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['meal_group_id', 'recipient_id']);
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plate_no', 20)->unique();
            $table->string('name');
            $table->string('vehicle_type', 20)->default('BOX')->comment('BOX,PICKUP,MOTOR,VAN');
            $table->unsignedInteger('capacity_portions')->default(0);
            $table->boolean('has_cooler')->default(false);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('position', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('supplier_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30)->default('WAREHOUSE');
            $table->text('address');
            $table->string('city', 100)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('supplier_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('payment_terms', 20)->default('CREDIT');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('supplier_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('moq', 15, 3)->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'ingredient_id']);
        });

        Schema::create('warehouse_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->string('zone_type', 20)->default('STORAGE');
            $table->timestamps();
            $table->unique(['warehouse_id', 'code']);
        });

        Schema::create('warehouse_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_zone_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->timestamps();
            $table->unique(['warehouse_zone_id', 'code']);
        });

        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_rack_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('barcode', 60)->unique()->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['warehouse_rack_id', 'code']);
        });

        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('center_type', 30)->default('COOKING');
            $table->unsignedInteger('capacity_per_hour')->default(0)->comment('porsi per jam');
            $table->unsignedInteger('operators_required')->default(1);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('delivery_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(0);
            $table->time('window_start')->nullable();
            $table->time('window_end')->nullable();
            $table->timestamps();
            $table->unique(['delivery_route_id', 'school_id']);
        });

        Schema::create('menu_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->unsignedTinyInteger('cycle_days')->default(5);
            $table->date('start_date');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->timestamps();
        });

        Schema::create('menu_cycle_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_cycle_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_no');
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['menu_cycle_id', 'day_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_cycle_days');
        Schema::dropIfExists('menu_cycles');
        Schema::dropIfExists('delivery_route_stops');
        Schema::dropIfExists('delivery_routes');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouse_racks');
        Schema::dropIfExists('warehouse_zones');
        Schema::dropIfExists('supplier_price_lists');
        Schema::dropIfExists('supplier_contracts');
        Schema::dropIfExists('supplier_addresses');
        Schema::dropIfExists('supplier_contacts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('meal_group_recipient');
        Schema::dropIfExists('meal_groups');
        Schema::dropIfExists('allergen_recipient');
        Schema::dropIfExists('allergen_ingredient');
        Schema::dropIfExists('allergens');
    }
};
