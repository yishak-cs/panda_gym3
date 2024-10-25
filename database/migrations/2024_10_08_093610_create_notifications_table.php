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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email', 'sms']);  // Notification type restricted to 'email' or 'sms'
            $table->text('message');  // The notification content
            $table->timestamp('sent_at')->nullable();  // When the notification was sent
            $table->enum('status', ['sent', 'failed']);  // Notification status: 'sent' or 'failed'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
