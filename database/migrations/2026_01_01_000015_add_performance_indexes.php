<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_stocks', function (Blueprint $table) {
            $table->index(['warehouse_id', 'item_type', 'item_id'], 'idx_stock_wh_item');
        });
        Schema::table('batches', function (Blueprint $table) {
            $table->index(['warehouse_id', 'status', 'expiry_date'], 'idx_batch_wh_status_exp');
            $table->index(['organization_id', 'status'], 'idx_batch_org_status');
        });
        Schema::table('production_orders', function (Blueprint $table) {
            $table->index(['central_kitchen_id', 'production_date', 'status'], 'idx_wo_kitchen_date_status');
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->index(['central_kitchen_id', 'delivery_date', 'status'], 'idx_dlv_kitchen_date_status');
            $table->index(['courier_id', 'status'], 'idx_dlv_courier_status');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['supplier_id', 'order_date'], 'idx_po_supplier_date');
        });
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->index(['ingredient_id', 'created_at'], 'idx_gri_ing_created');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->index(['organization_id', 'central_kitchen_id'], 'idx_users_org_kitchen');
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_audit_org_created');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', fn (Blueprint $t) => $t->dropIndex('idx_audit_org_created'));
        Schema::table('users', fn (Blueprint $t) => $t->dropIndex('idx_users_org_kitchen'));
        Schema::table('goods_receipt_items', fn (Blueprint $t) => $t->dropIndex('idx_gri_ing_created'));
        Schema::table('purchase_orders', fn (Blueprint $t) => $t->dropIndex('idx_po_supplier_date'));
        Schema::table('deliveries', fn (Blueprint $t) => $t->dropIndex('idx_dlv_courier_status'));
        Schema::table('deliveries', fn (Blueprint $t) => $t->dropIndex('idx_dlv_kitchen_date_status'));
        Schema::table('production_orders', fn (Blueprint $t) => $t->dropIndex('idx_wo_kitchen_date_status'));
        Schema::table('batches', fn (Blueprint $t) => $t->dropIndex('idx_batch_org_status'));
        Schema::table('batches', fn (Blueprint $t) => $t->dropIndex('idx_batch_wh_status_exp'));
        Schema::table('inventory_stocks', fn (Blueprint $t) => $t->dropIndex('idx_stock_wh_item'));
    }
};
