<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_flags', function (Blueprint $table) {
            // pending | approved | rejected — drives the Agent 4 approval gate.
            $table->string('decision')->default('pending')->after('resolved');
            $table->timestamp('resolved_at')->nullable()->after('decision');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_flags', function (Blueprint $table) {
            $table->dropColumn(['decision', 'resolved_at']);
        });
    }
};
