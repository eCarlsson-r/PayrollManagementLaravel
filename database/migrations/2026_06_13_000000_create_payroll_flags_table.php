<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores payroll entries flagged for human review by the compliance
     * agent (Agent 3) before payments are persisted by Agent 4.
     */
    public function up(): void
    {
        Schema::create('payroll_flags', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 8)->nullable();
            $table->string('period')->nullable();          // e.g. "2026-06"
            $table->text('reason');
            $table->string('severity')->default('warning'); // warning | critical
            $table->integer('gross_amount')->nullable();
            $table->integer('net_amount')->nullable();
            $table->json('data')->nullable();               // full offending payroll row
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_flags');
    }
};
