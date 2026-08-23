<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // INV-2026-001028
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('generated'); // draft | generated | partially_paid | paid | voided | refunded | partially_refunded
            $table->string('tax_mode')->default('exclusive'); // exclusive | inclusive
            $table->decimal('cgst_rate', 5, 4)->default(0.025);
            $table->decimal('sgst_rate', 5, 4)->default(0.025);
            $table->decimal('service_rate', 5, 4)->default(0.05);
            $table->boolean('service_removed')->default(false);
            $table->string('bill_discount_mode')->nullable(); // pct | amt
            $table->decimal('bill_discount_value', 10, 2)->nullable();
            $table->string('bill_discount_reason')->nullable();
            $table->foreignId('bill_discount_approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->unsignedInteger('loyalty_points_redeemed')->default(0);
            $table->decimal('loyalty_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_amount', 10, 2)->default(0);
            $table->boolean('is_gst_invoice')->default(false);
            $table->boolean('voided')->default(false);
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cash | upi | credit | debit | wallet | bank | other
            $table->decimal('amount', 10, 2);
            $table->decimal('tendered', 10, 2)->nullable(); // cash tendered, before change
            $table->string('reference')->nullable();
            $table->string('status')->default('success'); // success | refunded | voided
            $table->timestamp('paid_at')->useCurrent();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('mode'); // full | partial | item
            $table->string('reason');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('refunded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
