<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->date('plan_date')->index();
            $table->unsignedInteger('target_portions')->default(0);
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['central_kitchen_id', 'plan_date']);
        });

        Schema::create('production_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('planned_qty')->default(0);
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kitchen_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->date('production_date')->index();
            $table->decimal('planned_qty', 15, 3)->default(0);
            $table->decimal('produced_qty', 15, 3)->default(0);
            $table->decimal('rejected_qty', 15, 3)->default(0);
            $table->foreignId('unit_id')->constrained('units');
            $table->string('status', 20)->default('PLANNED')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['central_kitchen_id', 'production_date']);
            $table->index(['status', 'production_date']);
        });

        Schema::create('production_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_required', 15, 4);
            $table->decimal('qty_consumed', 15, 4)->default(0);
            $table->foreignId('unit_id')->constrained('units');
            $table->timestamps();
            $table->index('production_order_id');
        });

        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('reference_type', 100)->index()->comment('production_orders,goods_receipts');
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('number', 40)->unique();
            $table->date('check_date')->index();
            $table->string('check_type', 30)->default('ORGANOLEPTIC');
            $table->decimal('sample_qty', 12, 3)->default(0);
            $table->decimal('pass_qty', 12, 3)->default(0);
            $table->decimal('fail_qty', 12, 3)->default(0);
            $table->json('criteria')->nullable()->comment('skor per kriteria');
            $table->string('result', 20)->default('PENDING')->index();
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->date('packaging_date')->index();
            $table->unsignedInteger('packages_planned')->default(0);
            $table->unsignedInteger('packages_done')->default(0);
            $table->string('package_type', 30)->default('BOX');
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('packaging_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty_packed')->default(0);
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_items');
        Schema::dropIfExists('packagings');
        Schema::dropIfExists('quality_controls');
        Schema::dropIfExists('production_order_items');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_plan_items');
        Schema::dropIfExists('production_plans');
    }
};
