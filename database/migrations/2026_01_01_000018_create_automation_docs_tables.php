<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('event', 60)->comment('stock.low, stock.expiry, qc.failed, delivery.delayed, capa.overdue, invoice.variance, supplier.degraded');
            $table->json('conditions')->nullable()->comment('{"min_days": 3, "severity": "MAJOR"}');
            $table->string('action', 30)->comment('notify_role, webhook');
            $table->string('target_role', 40)->nullable();
            $table->string('message', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_fired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type', 60)->unique();
            $table->string('subject');
            $table->text('body')->comment('mendukung {{variable}}');
            $table->boolean('mail_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->boolean('in_app')->default(true);
            $table->boolean('mail')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'type']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30)->comment('SOP,WORK_INSTRUCTION,CHECKLIST,POLICY,FORM');
            $table->string('code', 30)->unique();
            $table->string('title');
            $table->text('content');
            $table->string('version', 10)->default('1.0');
            $table->date('effective_from')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status', 20)->default('DRAFT')->index()->comment('DRAFT,REVIEW,APPROVED,PUBLISHED,ARCHIVED');
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('document_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->timestamps();
            $table->unique(['document_id', 'user_id']);
        });

        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('entity', 40);
            $table->string('filename', 255);
            $table->string('status', 20)->default('COMPLETED')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('approval_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('approvable_type', 100)->comment('PO, PR, ...');
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('role', 40);
            $table->timestamps();
            $table->index(['approvable_type', 'min_amount']);
        });

        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delegator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegate_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['central_kitchen_id', 'code']);
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->date('last_maintenance_at')->nullable();
            $table->date('next_maintenance_at')->nullable()->index();
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type', 20)->comment('PREVENTIVE,CORRECTIVE');
            $table->text('description');
            $table->date('performed_at')->index();
            $table->decimal('cost', 15, 2)->default(0);
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->unsignedInteger('max_users')->default(0)->comment('0 = tanpa batas');
            $table->unsignedInteger('max_warehouses')->default(0);
            $table->boolean('api_access')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->nullable()->after('status')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_plan_id');
        });
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_matrices');
        Schema::dropIfExists('imports');
        Schema::dropIfExists('document_acknowledgements');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('automation_rules');
    }
};
