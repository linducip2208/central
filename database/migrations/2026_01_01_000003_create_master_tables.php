<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->string('unit_type', 20)->default('WEIGHT')->comment('WEIGHT,VOLUME,COUNT,LENGTH');
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('to_unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('factor', 15, 6)->comment('multiply from_qty to get to_qty');
            $table->timestamps();
            $table->unique(['from_unit_id', 'to_unit_id']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('category', 30)->default('FOOD')->comment('FOOD,NON_FOOD,SERVICE');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_number', 30)->nullable();
            $table->string('bank_account')->nullable();
            $table->unsignedTinyInteger('rating')->default(0);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'status']);
            $table->index('name');
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->nullable()->constrained()->nullOnDelete();
            $table->string('npsn', 30)->nullable()->unique()->comment('Nomor Pokok Sekolah Nasional');
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('level', 20)->default('SD')->comment('PAUD,TK,SD,SMP,SMA,SMK,SLB');
            $table->text('address')->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone', 30)->nullable();
            $table->unsignedInteger('student_count')->default(0);
            $table->unsignedInteger('target_portions')->default(0);
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->string('status', 20)->default('ACTIVE')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['central_kitchen_id', 'status']);
        });

        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('identifier', 50)->nullable()->comment('NIS/NISN');
            $table->string('grade', 20)->nullable();
            $table->string('class_name', 20)->nullable();
            $table->string('gender', 10)->nullable();
            $table->text('allergy_notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipients');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('units');
    }
};
