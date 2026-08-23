<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // RES-204
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name'); // denormalized so a reservation still reads fine if the customer record is later removed
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('date');
            $table->time('time');
            $table->unsignedInteger('guests');
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->string('status')->default('pending'); // pending | confirmed | arrived | seated | completed | cancelled | no_show
            $table->string('occasion')->default('None');
            $table->text('special_request')->nullable();
            $table->string('source')->default('Phone'); // Phone | Walk-in | Website | WhatsApp | Other
            $table->decimal('deposit', 10, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reservation_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_activities');
        Schema::dropIfExists('reservations');
    }
};
