<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('stage', 30)->comment('INCOMING,IN_PROCESS,FINISHED');
            $table->json('parameters')->comment('[{name,type,spec_min,spec_max,unit,required}]');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type', 100)->index();
            $table->unsignedBigInteger('reference_id')->index();
            $table->string('number', 40)->unique();
            $table->date('inspection_date')->index();
            $table->json('results')->nullable()->comment('[{parameter,measured,pass}]');
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('result', 20)->default('PENDING')->index();
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('temperature_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('checkpoint', 60)->comment('COLD_STORAGE,COOKING,COOLING,SERVING,TRANSPORT');
            $table->decimal('temperature_c', 5, 2);
            $table->decimal('spec_min', 5, 2)->nullable();
            $table->decimal('spec_max', 5, 2)->nullable();
            $table->boolean('in_spec')->default(true)->index();
            $table->timestamp('logged_at')->index();
            $table->text('corrective_action')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('non_conformances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quality_inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->string('category', 40)->comment('MATERIAL,PROCESS,HYGIENE,EQUIPMENT,FOREIGN_OBJECT,OTHER');
            $table->string('severity', 20)->default('MINOR')->comment('MINOR,MAJOR,CRITICAL');
            $table->text('description');
            $table->string('disposition', 30)->default('HOLD')->comment('HOLD,REWORK,REJECT,RELEASE');
            $table->string('status', 20)->default('OPEN')->index();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('capa_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('non_conformance_id')->constrained()->cascadeOnDelete();
            $table->string('action_type', 20)->comment('CORRECTIVE,PREVENTIVE');
            $table->text('action');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status', 20)->default('OPEN')->index();
            $table->timestamps();
        });

        Schema::create('recalls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trigger_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('number', 40)->unique();
            $table->string('reason', 40)->comment('CONTAMINATION,ALLERGEN,FOREIGN_OBJECT,SPOILAGE,OTHER');
            $table->string('severity', 20)->default('CLASS_II');
            $table->text('description');
            $table->string('status', 20)->default('DRAFT')->index()->comment('DRAFT,ACTIVE,CONTAINED,CLOSED');
            $table->text('actions_taken')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recall_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->decimal('stock_on_hand', 15, 3)->default(0);
            $table->decimal('qty_delivered', 15, 3)->default(0);
            $table->string('action', 30)->default('QUARANTINE');
            $table->timestamps();
            $table->unique(['recall_id', 'batch_id']);
        });

        Schema::create('production_order_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role', 30)->default('OPERATOR');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['production_order_id', 'user_id']);
        });

        Schema::create('downtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable();
            $table->string('reason', 60);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_material_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packaging_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete()->comment('bahan kategori PACKAGING');
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('qty_used', 15, 3);
            $table->timestamps();
        });

        Schema::create('school_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('expected_qty')->default(0);
            $table->unsignedInteger('received_qty')->default(0);
            $table->unsignedInteger('rejected_qty')->default(0);
            $table->unsignedInteger('attendance')->default(0);
            $table->text('complaint')->nullable();
            $table->text('feedback')->nullable();
            $table->string('status', 20)->default('SUBMITTED')->index();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['delivery_id', 'school_id']);
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('url');
            $table->json('events');
            $table->string('secret', 100);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event', 60)->index();
            $table->json('payload');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->string('status', 20)->default('PENDING')->index();
            $table->text('last_error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'attempts']);
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->boolean('is_enabled')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('school_confirmations');
        Schema::dropIfExists('packaging_material_usages');
        Schema::dropIfExists('downtimes');
        Schema::dropIfExists('production_order_operators');
        Schema::dropIfExists('recall_items');
        Schema::dropIfExists('recalls');
        Schema::dropIfExists('capa_actions');
        Schema::dropIfExists('non_conformances');
        Schema::dropIfExists('temperature_logs');
        Schema::dropIfExists('quality_inspections');
        Schema::dropIfExists('inspection_templates');
    }
};
