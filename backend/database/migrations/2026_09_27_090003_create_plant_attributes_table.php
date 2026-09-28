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
        Schema::create('plant_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->string('light_requirement', 50); // e.g. Direct Sun, Bright Indirect, Low Light
            $table->string('watering_frequency', 50); // e.g. Every 2-3 days, Weekly, Dry out completely
            $table->string('difficulty_level', 30)->default('Beginner Friendly'); // Beginner Friendly, Moderate, Expert
            $table->boolean('pet_friendly')->default(false);
            $table->boolean('air_purifying')->default(false);
            $table->string('mature_size', 100)->nullable();
            $table->string('seasonality', 100)->nullable();
            $table->string('growth_rate', 50)->default('Moderate'); // Slow, Moderate, Fast
            $table->string('ideal_temperature_range', 50)->nullable(); // e.g. 18°C - 28°C
            $table->string('humidity_requirement', 50)->nullable(); // e.g. High (60%+), Average (40-60%)
            $table->decimal('pot_diameter_inches', 4, 1)->nullable(); // To pair with planters
            $table->json('care_instructions')->nullable(); // Seasonal guide, repotting, pruning tips
            $table->timestamps();

            $table->index('light_requirement');
            $table->index('watering_frequency');
            $table->index('difficulty_level');
            $table->index('pet_friendly');
            $table->index('air_purifying');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_attributes');
    }
};
