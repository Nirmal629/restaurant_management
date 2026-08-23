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
            $table->string('code')->unique(); // ING-001
            $table->string('name');
            $table->string('category'); // Grains & Rice, Meat & Poultry, ...
            $table->string('unit'); // KG, GRAM, LITRE, ML, PCS, PACK, BOX, BOTTLE
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('min_stock', 12, 3)->default(0);
            $table->decimal('reorder_level', 12, 3)->default(0);
            $table->decimal('avg_cost', 10, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('storage_location')->nullable();
            $table->timestamps();
        });

        // status (in/low/out) is derived from current_stock vs min_stock at read time —
        // not stored, so it can never drift out of sync with the actual quantity.

        Schema::create('stock_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // OPENING | PURCHASE | CONSUMPTION | WASTAGE | ADJUSTMENT | RETURN | TRANSFER
            $table->string('reference')->nullable(); // ORD-1028, GRN-2026-0042, WST-014, …
            $table->decimal('previous_qty', 12, 3);
            $table->decimal('change_qty', 12, 3); // signed
            $table->decimal('new_qty', 12, 3);
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('wastages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // WST-014
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('qty', 12, 3);
            $table->string('unit');
            $table->string('reason'); // Expired, Damaged, Preparation Waste, Overcooked, Spillage, Other
            $table->decimal('cost', 10, 2)->default(0);
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SC-2026-004
            $table->date('date');
            $table->string('status')->default('draft'); // draft | completed
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_count_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('system_qty', 12, 3);
            $table->decimal('physical_qty', 12, 3);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // Recipe / BOM — one recipe per menu item, driving food-cost estimates.
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('recipe_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('qty', 12, 4);
            $table->string('unit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_lines');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('wastages');
        Schema::dropIfExists('stock_ledger_entries');
        Schema::dropIfExists('ingredients');
    }
};
