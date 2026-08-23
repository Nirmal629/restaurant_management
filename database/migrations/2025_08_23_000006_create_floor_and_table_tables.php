<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name'); // Ground Floor, First Floor, Outdoor, VIP Section
            $table->text('description')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique(); // T01, V03
            $table->string('name')->nullable();
            $table->unsignedInteger('seats');
            $table->string('shape')->default('square'); // square | rect | round
            $table->string('status')->default('available'); // available | occupied | reserved | billing | cleaning | disabled
            $table->foreignId('merged_with_table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->boolean('is_merge_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('floors');
    }
};
