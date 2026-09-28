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
        Schema::create('user_plants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('nickname', 100);
            $table->date('adopted_at')->default(now()->toDateString());
            $table->timestamp('last_watered_at')->nullable();
            $table->timestamp('last_fertilized_at')->nullable();
            $table->timestamp('last_repotted_at')->nullable();
            $table->integer('reminder_frequency_days')->default(7);
            $table->text('notes')->nullable();
            $table->string('photo_url', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_watered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_plants');
    }
};
