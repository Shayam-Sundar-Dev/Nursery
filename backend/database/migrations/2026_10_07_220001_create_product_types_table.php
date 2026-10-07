<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 60)->nullable()->default('leaf');
            $table->string('badge_color', 30)->default('emerald');
            $table->boolean('requires_botanical_attributes')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        // Seed initial default types that match the existing system
        $defaultTypes = [
            [
                'name' => 'Live Houseplant / Botanical',
                'slug' => 'plant',
                'description' => 'Living potted flora, tropical specimens, and air-purifying foliage.',
                'icon' => 'leaf',
                'badge_color' => 'emerald',
                'requires_botanical_attributes' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pot & Ceramic Planter',
                'slug' => 'planter',
                'description' => 'Breathable terracotta, artisanal ceramics, and self-watering pots.',
                'icon' => 'box',
                'badge_color' => 'amber',
                'requires_botanical_attributes' => false,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Soil & Plant Nutrition',
                'slug' => 'soil_fertilizer',
                'description' => 'Chunky aroid potting mixes, organic worm castings, and liquid feeds.',
                'icon' => 'layers',
                'badge_color' => 'stone',
                'requires_botanical_attributes' => false,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Garden Tool & Accessory',
                'slug' => 'accessory',
                'description' => 'Precision pruning shears, brass misters, moss poles, and moisture meters.',
                'icon' => 'wrench',
                'badge_color' => 'blue',
                'requires_botanical_attributes' => false,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Seeds & Germination',
                'slug' => 'seed',
                'description' => 'Heirloom seeds, propagation cuttings, and nursery bulb varieties.',
                'icon' => 'sprout',
                'badge_color' => 'teal',
                'requires_botanical_attributes' => false,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Botanical Care Bundle',
                'slug' => 'care_bundle',
                'description' => 'Curated starter plant kits, potting mats, and plant protection packages.',
                'icon' => 'package',
                'badge_color' => 'purple',
                'requires_botanical_attributes' => false,
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('product_types')->insert($defaultTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_types');
    }
};

