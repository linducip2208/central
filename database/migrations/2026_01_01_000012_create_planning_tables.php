<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20)->comment('product (parent selalu produk jadi)');
            $table->unsignedBigInteger('item_id');
            $table->string('code', 30)->unique();
            $table->string('version', 10)->default('1.0');
            $table->date('effective_from')->nullable()->index();
            $table->date('effective_to')->nullable();
            $table->decimal('yield_qty', 12, 3)->default(1);
            $table->string('status', 20)->default('DRAFT')->index()->comment('DRAFT,ACTIVE,ARCHIVED');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['item_type', 'item_id', 'status']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_bom_item_id')->nullable()->constrained('bom_items')->nullOnDelete()->comment('untuk level lebih dalam (sub-assembly)');
            $table->string('component_type', 20)->comment('ingredient,product(sub-assembly),material');
            $table->unsignedBigInteger('component_id');
            $table->decimal('qty', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('scrap_pct', 5, 2)->default(0);
            $table->decimal('waste_pct', 5, 2)->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bom_id', 'level']);
            $table->index(['component_type', 'component_id']);
        });

        Schema::create('demand_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40)->unique();
            $table->string('period_type', 10)->comment('DAILY,WEEKLY,MONTHLY');
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('demand_plan_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demand_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->date('demand_date')->index();
            $table->unsignedInteger('gross_demand')->default(0);
            $table->unsignedInteger('attendance_adjustment')->default(0);
            $table->unsignedInteger('manual_adjustment')->default(0);
            $table->unsignedInteger('adjusted_demand')->default(0);
            $table->unsignedInteger('safety_stock')->default(0);
            $table->unsignedInteger('net_demand')->default(0);
            $table->string('source', 20)->default('FORECAST');
            $table->timestamps();
            $table->index('demand_plan_id');
        });

        Schema::create('mrp_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('demand_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40)->unique();
            $table->date('run_date')->index();
            $table->string('status', 20)->default('COMPLETED')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mrp_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mrp_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('gross_requirement', 15, 3)->default(0);
            $table->decimal('on_hand', 15, 3)->default(0);
            $table->decimal('reserved', 15, 3)->default(0);
            $table->decimal('available', 15, 3)->default(0);
            $table->decimal('incoming', 15, 3)->default(0);
            $table->decimal('safety_stock', 15, 3)->default(0);
            $table->decimal('net_requirement', 15, 3)->default(0);
            $table->decimal('suggested_order_qty', 15, 3)->default(0);
            $table->foreignId('suggested_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('suggested_price', 15, 2)->default(0);
            $table->string('recommendation', 30)->default('NONE')->comment('NONE,PURCHASE,TRANSFER,PRODUCE');
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->index('mrp_run_id');
        });

        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->date('rfq_date')->index();
            $table->date('deadline')->nullable();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 15, 3);
            $table->foreignId('unit_id')->constrained('units');
            $table->timestamps();
        });

        Schema::create('rfq_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rfq_id', 'supplier_id']);
        });

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40)->unique();
            $table->date('quotation_date')->index();
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->string('status', 20)->default('SUBMITTED')->index()->comment('SUBMITTED,SELECTED,REJECTED,EXPIRED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('qty_offered', 15, 3);
            $table->timestamps();
        });

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supplier_invoice_no', 60);
            $table->string('number', 40)->unique()->comment('nomor internal');
            $table->date('invoice_date')->index();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('qty_variance', 15, 3)->default(0);
            $table->decimal('price_variance', 15, 2)->default(0);
            $table->string('match_status', 20)->default('UNMATCHED')->index()->comment('UNMATCHED,MATCHED,VARIANCE');
            $table->string('payment_status', 20)->default('UNPAID')->index();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['supplier_id', 'supplier_invoice_no']);
        });

        Schema::create('supplier_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 15, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('approvable_type', 100)->index();
            $table->unsignedBigInteger('approvable_id')->index();
            $table->string('action', 30)->comment('APPROVE,REJECT');
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('status', 20)->default('APPROVED')->index();
            $table->text('comment')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['approvable_type', 'approvable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('supplier_invoice_items');
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('rfq_suppliers');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
        Schema::dropIfExists('mrp_lines');
        Schema::dropIfExists('mrp_runs');
        Schema::dropIfExists('demand_plan_lines');
        Schema::dropIfExists('demand_plans');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
    }
};
