<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Restaurant Owner, Restaurant Manager, Cashier, Waiter, Kitchen Manager, Chef, Inventory Manager
            $table->timestamps();
        });

        // One row per (module, action) — e.g. (Inventory, Approve). The full matrix
        // shown in Employees → Permissions is built from these.
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // POS, Orders, Kitchen, Billing, Customers, Menu, Inventory, Purchases, Expenses, Reports, Employees, Settings
            $table->string('action');  // View, Create, Edit, Cancel, Approve, Refund, Export
            $table->timestamps();
            $table->unique(['module', 'action']);
        });

        // Role-level defaults.
        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
