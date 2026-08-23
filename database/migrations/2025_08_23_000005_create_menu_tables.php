<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Main Kitchen, Tandoor, Chinese, Beverage, Dessert, Bar
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->foreignId('menu_category_id')->constrained()->restrictOnDelete();
            $table->string('subcategory')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('diet_type'); // veg | nonveg | egg
            $table->decimal('base_price', 10, 2);
            $table->string('tax_profile')->default('GST 5%');
            $table->unsignedInteger('prep_time_minutes')->default(10);
            $table->foreignId('kitchen_station_id')->nullable()->constrained()->nullOnDelete();
            $table->string('availability')->default('available'); // available | sold_out | temp_unavailable
            $table->boolean('featured')->default(false);
            $table->boolean('popular')->default(false);
            $table->boolean('stock_tracked')->default(false);
            $table->boolean('online_visible')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // Regular, Large, Family
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Spice Level, Add-Ons
            $table->string('type'); // single | multi
            $table->boolean('required')->default(false);
            $table->unsignedInteger('min_select')->default(0);
            $table->unsignedInteger('max_select')->default(1);
            $table->timestamps();
        });

        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // Mild, Extra Chicken
            $table->decimal('price_delta', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('menu_item_modifier_group', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_item_id', 'modifier_group_id']);
        });

        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_items');
        Schema::dropIfExists('combos');
        Schema::dropIfExists('menu_item_modifier_group');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifier_groups');
        Schema::dropIfExists('menu_item_variants');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('kitchen_stations');
        Schema::dropIfExists('menu_categories');
    }
};
