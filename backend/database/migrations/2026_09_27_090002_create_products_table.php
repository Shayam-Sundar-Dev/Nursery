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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name', 255);
            $table->string('botanical_name', 255)->nullable();
            $table->string('slug', 280)->unique();
            $table->string('type', 50)->default('plant'); // plant, seed, planter, soil_fertilizer, tool, care_bundle
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('primary_image_url', 500)->nullable();
            $table->json('gallery_images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->boolean('requires_special_shipping')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('is_published');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
