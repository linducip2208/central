<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->date('waste_date')->index();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty', 15, 3);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('cost_loss', 15, 2)->default(0);
            $table->string('reason', 40)->index()->comment('EXPIRED,SPOILED,OVER_PRODUCTION,QC_REJECT,OTHER');
            $table->string('disposal_method', 40)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['warehouse_id', 'waste_date']);
            $table->index(['item_type', 'item_id']);
        });

        Schema::create('costings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->date('costing_date')->index();
            $table->decimal('material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('packaging_cost', 15, 2)->default(0);
            $table->decimal('delivery_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->unsignedInteger('portions')->default(0);
            $table->decimal('cost_per_portion', 15, 2)->default(0);
            $table->string('method', 10)->default('AVG');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['central_kitchen_id', 'costing_date']);
        });

        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20);
            $table->string('period', 10)->comment('YYYYMM');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['prefix', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_counters');
        Schema::dropIfExists('costings');
        Schema::dropIfExists('wastes');
    }
};
