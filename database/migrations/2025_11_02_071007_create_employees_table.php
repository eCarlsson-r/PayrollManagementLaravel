<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->string("id", 8);
            $table->text("first_name");
            $table->text("last_name");
            $table->string("position", 100);
            $table->date("dob");
            $table->text("email");
            $table->string("contact", 20);
            $table->text("address");
            $table->text("pay_method");
            $table->text("bank");
            $table->string("bank_account", 50);
            $table->text("manager");
            $table->unique("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
