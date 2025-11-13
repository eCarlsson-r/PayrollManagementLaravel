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
        Schema::create('scheme', function (Blueprint $table) {
            $table->id();
            $table->text("employee_id");
            $table->text("scheme");
            $table->float("base_amount");
            $table->float("base_commission_rate");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme');
    }
};
