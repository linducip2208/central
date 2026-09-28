<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->decimal('qty', 15, 3);
            $table->string('reason', 60);
            $table->string('status', 20)->default('COMPLETED')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->unsignedInteger('version')->default(1);
            $table->string('method', 20)->default('MOVING_AVG');
            $table->decimal('confidence', 5, 2)->default(0.7);
            $table->decimal('forecast_qty', 15, 2)->default(0);
            $table->decimal('actual_qty', 15, 2)->nullable();
            $table->decimal('error_pct', 8, 2)->nullable()->comment('MAPE per baris');
            $table->boolean('is_scenario')->default(false)->index();
            $table->string('scenario_name', 80)->nullable();
            $table->decimal('scenario_factor', 8, 4)->default(1);
            $table->string('status', 20)->default('DRAFT')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'period_start', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
        Schema::dropIfExists('supplier_returns');
    }
};
