<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // login credentials, when this employee has portal access
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique(); // EMP-001
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('shift')->default('fullday'); // morning | evening | fullday
            $table->string('status')->default('active'); // active | inactive | suspended
            $table->string('pos_pin_hash')->nullable(); // never store/display the raw PIN
            $table->json('permission_overrides')->nullable(); // { "Inventory": ["View","Edit"], ... } — employee-specific override of role defaults
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
