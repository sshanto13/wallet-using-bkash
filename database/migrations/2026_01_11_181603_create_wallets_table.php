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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_id')->nullable();
            $table->string('agreement_id')->nullable();
            $table->text('bkash_url')->nullable();
            $table->text('callback_url')->nullable();
            $table->text('success_callback_url')->nullable();
            $table->text('failure_callback_url')->nullable();
            $table->text('cancelled_callback_url')->nullable();
            $table->string('payer_reference')->nullable();
            $table->string('agreement_status')->nullable();
            $table->string('agreement_create_time')->nullable();
            $table->string('signature')->nullable();
            $table->text('token')->nullable();
            $table->string('masked');
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
