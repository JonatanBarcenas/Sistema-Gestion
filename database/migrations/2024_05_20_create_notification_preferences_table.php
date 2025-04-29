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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->boolean('project_created')->default(true);
            $table->boolean('project_updated')->default(true);
            $table->boolean('project_status_changed')->default(true);
            $table->boolean('project_comment_added')->default(true);
            $table->boolean('project_completed')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('database_notifications')->default(true);
            $table->timestamps();
            
            // Asegurar que solo haya una preferencia por cliente
            $table->unique('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};