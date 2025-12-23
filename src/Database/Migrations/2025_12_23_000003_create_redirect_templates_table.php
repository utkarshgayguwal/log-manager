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
        Schema::create('redirect_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // E.g. 'client_profile', 'payment_edit'
            $table->string('path'); // E.g. /clients/[clientID]/profile?prev_page=system-logs
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirect_templates');
    }
};