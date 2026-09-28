<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mrp_lines', function (Blueprint $table) {
            $table->decimal('expiring_soon', 15, 3)->default(0)->after('incoming');
            $table->foreignId('transfer_from_warehouse_id')->nullable()->after('suggested_supplier_id')->constrained('warehouses')->nullOnDelete();
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('order_multiple', 15, 3)->default(0)->after('moq');
            $table->string('barcode', 60)->nullable()->unique()->after('code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 60)->nullable()->unique()->after('code');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('fifo_method', 10)->default('FEFO')->after('warehouse_type')->comment('FEFO,FIFO');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('work_center_id')->constrained()->nullOnDelete();
            $table->decimal('rework_qty', 15, 3)->default(0)->after('rejected_qty');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
            $table->dropColumn('rework_qty');
        });
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('fifo_method');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['order_multiple', 'barcode']);
        });
        Schema::table('mrp_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transfer_from_warehouse_id');
            $table->dropColumn('expiring_soon');
        });
    }
};
