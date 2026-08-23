<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ORD-1028
            $table->string('type')->default('dinein'); // dinein | takeaway | delivery
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('waiter_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->unsignedInteger('guests')->nullable();
            $table->string('token')->nullable(); // takeaway token / delivery reference
            $table->string('status')->default('open'); // open | billing | paid | cancelled
            $table->timestamp('started_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
            $table->string('variant_label')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->json('modifiers')->nullable(); // [{ "label": "Extra Cheese", "delta": 30 }, ...]
            $table->text('note')->nullable();
            $table->string('allergy_note')->nullable();
            $table->foreignId('kitchen_station_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('kot_round')->default(1);
            $table->string('status')->default('unsent'); // unsent | sent | accepted | preparing | ready | served | cancelled | unavailable
            $table->string('unavailable_reason')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kots', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // KOT-1045
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('round')->default(1);
            $table->string('printer')->nullable();
            $table->timestamp('sent_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kots');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
