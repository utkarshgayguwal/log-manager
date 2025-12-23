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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();

            $table->morphs('loggable');

            $table->unsignedBigInteger('module_id')->nullable();
            $table->unsignedBigInteger('program_id')->nullable();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->boolean('is_redirect_enabled')->default(1);
            $table->string('action')->nullable();
            $table->string('log_type')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('redirect_path')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Note: Removed foreign key constraints for package compatibility
            // Apps can add their own foreign key constraints if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};