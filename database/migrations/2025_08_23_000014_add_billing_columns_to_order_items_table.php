<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Billing-time adjustments are a separate dimension from the KOT/kitchen
     * lifecycle (`status`) already on this table — an item can be `served`
     * in the kitchen sense and still be marked complimentary or discounted
     * at the bill. Kept nullable/default-normal so existing rows are unaffected.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('bill_status')->default('normal')->after('status'); // normal | complimentary | discounted | refunded
            $table->decimal('bill_discount_amount', 10, 2)->default(0)->after('bill_status');
            $table->string('comp_reason')->nullable()->after('bill_discount_amount');
            $table->foreignId('comp_by')->nullable()->after('comp_reason')->constrained('employees')->nullOnDelete();
            $table->string('refund_reason')->nullable()->after('comp_by');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('refund_reason');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('comp_by');
            $table->dropColumn(['bill_status', 'bill_discount_amount', 'comp_reason', 'refund_reason', 'refund_amount']);
        });
    }
};
