<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete()->comment('bahan utama');
            $table->foreignId('substitute_id')->constrained('ingredients')->cascadeOnDelete()->comment('bahan pengganti');
            $table->decimal('ratio', 10, 4)->default(1)->comment('1 unit utama = ratio unit pengganti');
            $table->text('notes')->nullable();
            $table->boolean('is_approved')->default(false)->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['ingredient_id', 'substitute_id']);
        });

        Schema::create('hygiene_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->constrained()->cascadeOnDelete();
            $table->string('check_type', 20)->comment('CLEANING,SANITATION,EQUIPMENT');
            $table->string('area', 80);
            $table->json('items')->comment('[{item, pass}]');
            $table->string('photo_path')->nullable();
            $table->string('result', 20)->default('PASSED')->index();
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hygiene_checks');
        Schema::dropIfExists('ingredient_substitutions');
    }
};
