<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('category', 30)->default('STAPLE')->comment('STAPLE,PROTEIN,VEGETABLE,FRUIT,SPICE,OIL,OTHER');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('standard_price', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->decimal('max_stock', 15, 3)->default(0);
            $table->unsignedInteger('shelf_life_days')->default(0);
            $table->boolean('requires_batch')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'is_active']);
            $table->index('name');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('category', 30)->default('MEAL')->comment('MEAL,SNACK,DRINK,EXTRA');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('standard_cost', 15, 2)->default(0);
            $table->unsignedInteger('portion_size_gram')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('central_kitchen_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->date('menu_date')->index();
            $table->string('meal_type', 20)->default('LUNCH')->comment('BREAKFAST,LUNCH,SNACK');
            $table->unsignedInteger('planned_portions')->default(0);
            $table->decimal('budget_per_portion', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('DRAFT')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['central_kitchen_id', 'menu_date']);
        });

        Schema::create('menu_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_per_portion', 12, 3)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['menu_id', 'product_id']);
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('version', 10)->default('1.0');
            $table->decimal('yield_qty', 12, 3)->default(1)->comment('hasil dalam satuan produk');
            $table->foreignId('yield_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->text('instructions')->nullable();
            $table->unsignedInteger('cook_time_minutes')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['product_id', 'is_active']);
        });

        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 15, 4);
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('waste_factor_pct', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['recipe_id', 'ingredient_id']);
        });

        Schema::create('nutrition_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('calories', 10, 2)->default(0);
            $table->decimal('protein_g', 10, 2)->default(0);
            $table->decimal('carbs_g', 10, 2)->default(0);
            $table->decimal('fat_g', 10, 2)->default(0);
            $table->decimal('fiber_g', 10, 2)->default(0);
            $table->decimal('sugar_g', 10, 2)->default(0);
            $table->decimal('sodium_mg', 10, 2)->default(0);
            $table->decimal('serving_size_g', 10, 2)->default(0);
            $table->string('source', 30)->default('MANUAL');
            $table->timestamps();
            $table->index(['product_id']);
            $table->index(['menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_records');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('menu_products');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('products');
        Schema::dropIfExists('ingredients');
    }
};
