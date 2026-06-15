<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_messages', function (Blueprint $table) {
            $table->id();
            $table->string('period')->nullable()->index(); // e.g. "2026-06" — correlates a run
            $table->string('agent_name')->nullable();       // "Data Collector", ...
            $table->string('sender_type')->default('Agent'); // Agent | System | User
            $table->string('message_type')->default('message'); // message | thought | error | handoff | report
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_messages');
    }
};
