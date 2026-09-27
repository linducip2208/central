<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20)->comment('ingredient,product');
            $table->unsignedBigInteger('item_id');
            $table->string('batch_no', 60);
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 30)->default('PURCHASE')->comment('PURCHASE,PRODUCTION,TRANSFER,ADJUSTMENT');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('initial_qty', 15, 3)->default(0);
            $table->decimal('remaining_qty', 15, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('status', 20)->default('AVAILABLE')->index()->comment('AVAILABLE,BLOCKED,EXPIRED,DEPLETED');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['warehouse_id', 'batch_no']);
            $table->index(['item_type', 'item_id']);
            $table->index(['warehouse_id', 'expiry_date', 'status']);
            $table->index(['status', 'expiry_date']);
        });

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty', 15, 3)->default(0);
            $table->decimal('reserved_qty', 15, 3)->default(0);
            $table->decimal('avg_cost', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'item_type', 'item_id', 'batch_id'], 'inv_stock_unique');
            $table->index(['item_type', 'item_id']);
            $table->index(['warehouse_id', 'item_type']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->string('movement_type', 30)->index()->comment('PURCHASE_RECEIPT,STOCK_IN,STOCK_OUT,PRODUCTION_CONSUMPTION,PRODUCTION_OUTPUT,TRANSFER,ADJUSTMENT,STOCK_OPNAME,WASTE,DELIVERY,RETURN');
            $table->string('direction', 5)->comment('IN,OUT');
            $table->decimal('qty', 15, 3);
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('qty_base', 15, 3)->comment('qty dalam satuan dasar item');
            $table->decimal('stock_before', 15, 3)->default(0);
            $table->decimal('stock_after', 15, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('reference_type', 100)->nullable()->index();
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->string('reference_no', 60)->nullable()->index();
            $table->date('movement_date')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['warehouse_id', 'item_type', 'item_id', 'movement_date'], 'inv_mov_wh_item_date');
            $table->index(['reference_type', 'reference_id']);
            $table->index(['created_at']);
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40)->unique();
            $table->date('opname_date')->index();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['warehouse_id', 'status']);
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20);
            $table->unsignedBigInteger('item_id');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('system_qty', 15, 3)->default(0);
            $table->decimal('physical_qty', 15, 3)->default(0);
            $table->decimal('difference', 15, 3)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('stock_opname_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('batches');
    }
};
