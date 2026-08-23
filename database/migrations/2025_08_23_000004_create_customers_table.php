<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('birthday')->nullable();
            $table->date('anniversary')->nullable();
            $table->string('address')->nullable();
            $table->string('gstin')->nullable();
            $table->string('business_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('allergies')->nullable();
            $table->json('tags')->nullable(); // ["Regular","Foodie",...]
            $table->boolean('is_vip')->default(false);
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->date('joined_date')->nullable();
            $table->timestamps();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
