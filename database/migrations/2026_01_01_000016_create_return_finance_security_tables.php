<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('qty')->default(0);
            $table->string('reason', 60)->comment('DAMAGED,WRONG_MENU,EXCESS,REFUSED,OTHER');
            $table->string('condition', 20)->default('GOOD')->comment('GOOD,DAMAGED,EXPIRED');
            $table->string('disposition', 20)->default('PENDING')->comment('PENDING,RESTOCKED,WASTED');
            $table->string('status', 20)->default('RECEIVED')->index();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['delivery_id', 'status']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('distance_km');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('geofence_radius_m', 8, 1)->default(300)->after('longitude');
        });

        Schema::table('central_kitchens', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('daily_capacity');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::create('kitchen_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7)->comment('YYYY-MM');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['central_kitchen_id', 'period']);
        });

        Schema::create('period_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->nullable()->constrained()->nullOnDelete();
            $table->date('closed_before')->comment('transaksi < tanggal ini dikunci');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['organization_id', 'central_kitchen_id']);
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('dataset', 30);
            $table->string('frequency', 10)->comment('DAILY,WEEKLY,MONTHLY');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret', 100)->nullable()->after('remember_token');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_confirmed_at']);
        });
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('period_closings');
        Schema::dropIfExists('kitchen_budgets');
        Schema::table('central_kitchens', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'geofence_radius_m']);
        });
        Schema::dropIfExists('delivery_returns');
    }
};
