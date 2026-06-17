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
        if (Schema::hasTable('payslips')) {
            return;
        }

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('period')->index();
            $table->unsignedBigInteger('gross_amount');
            $table->unsignedBigInteger('pph21')->default(0);
            $table->unsignedBigInteger('bpjs_total')->default(0);
            $table->unsignedBigInteger('net_amount');
            $table->unsignedBigInteger('corrected_amount')->nullable();
            $table->string('file_path');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
