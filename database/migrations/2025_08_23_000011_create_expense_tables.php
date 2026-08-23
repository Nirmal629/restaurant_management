<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // EXP-2026-0038
            $table->date('date');
            $table->string('category'); // Rent, Electricity, Gas, ...
            $table->text('description');
            $table->string('vendor')->nullable();
            $table->string('payment_method'); // Cash, UPI, Card, Bank Transfer, Other
            $table->decimal('amount', 12, 2);
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft'); // draft | approved | rejected | paid
            $table->boolean('receipt_attached')->default(false);
            $table->string('reference')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_activities');
        Schema::dropIfExists('expenses');
    }
};
