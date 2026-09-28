<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\HomeSlider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PlantAttribute;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserPlant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NurseryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Demo Shopper / Gardener & Full Administrative Team with RBAC
        $user = User::firstOrCreate(
            ['email' => 'gardener@nursery.test'],
            [
                'name' => 'Flora Vance',
                'password' => Hash::make('password123'),
                'is_admin' => false,
                'role' => AdminRole::Customer,
                'is_active' => true,
            ]
        );

        // Super Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@nursery.test'],
            [
                'name' => 'Elena Vance (Super Admin)',
                'password' => Hash::make('admin1234'),
                'is_admin' => true,
                'role' => AdminRole::SuperAdmin,
                'is_active' => true,
            ]
        );
        $admin->role = AdminRole::SuperAdmin;
        $admin->is_admin = true;
        $admin->is_active = true;
        $admin->save();

        // Head Botanist / Catalog Manager
        $botanist = User::firstOrCreate(
            ['email' => 'botanist@nursery.test'],
            [
                'name' => 'Dr. Julian Thorne (Botanist)',
                'password' => Hash::make('botanist1234'),
                'is_admin' => true,
                'role' => AdminRole::Botanist,
                'is_active' => true,
            ]
        );
        $botanist->role = AdminRole::Botanist;
        $botanist->is_admin = true;
        $botanist->is_active = true;
        $botanist->save();

        // Logistics & Dispatch Officer
        $dispatch = User::firstOrCreate(
            ['email' => 'dispatch@nursery.test'],
            [
                'name' => 'Marcus Sterling (Fulfillment Officer)',
                'password' => Hash::make('dispatch1234'),
                'is_admin' => true,
                'role' => AdminRole::Fulfillment,
                'is_active' => true,
            ]
        );
        $dispatch->role = AdminRole::Fulfillment;
        $dispatch->is_admin = true;
        $dispatch->is_active = true;
        $dispatch->save();

        // Customer Care & Plant Advisor
        $support = User::firstOrCreate(
            ['email' => 'support@nursery.test'],
            [
                'name' => 'Aria Song (Plant Care Advisor)',
                'password' => Hash::make('support1234'),
                'is_admin' => true,
                'role' => AdminRole::Support,
                'is_active' => true,
            ]
        );
        $support->role = AdminRole::Support;
        $support->is_admin = true;
        $support->is_active = true;
        $support->save();

        // 2. Categories
        $indoorCat = Category::firstOrCreate(
            ['slug' => 'indoor-plants'],
            [
                'name' => 'Indoor Plants',
                'description' => 'Lush tropical houseplants curated for home & office light conditions.',
            ]
        );

        $lowLightCat = Category::firstOrCreate(
            ['slug' => 'low-light-plants'],
            [
                'parent_id' => $indoorCat->id,
                'name' => 'Low Light Champions',
                'description' => 'Hardy botanical specimens that thrive in dim corners and apartments.',
            ]
        );

        $plantersCat = Category::firstOrCreate(
            ['slug' => 'pots-and-planters'],
            [
                'name' => 'Pots & Planters',
                'description' => 'Breathable terracotta, glazed ceramic, and self-watering pots.',
            ]
        );

        $careCat = Category::firstOrCreate(
            ['slug' => 'soil-and-nutrition'],
            [
                'name' => 'Soil & Plant Nutrition',
                'description' => 'Chunky aroid potting mixes, perlite, and organic seaweed fertilizers.',
            ]
        );

        // 3. Products & Inventory (Only seed if not already created)
        if (Product::where('slug', 'monstera-deliciosa')->doesntExist()) {
            $monstera = Product::create([
                'category_id' => $indoorCat->id,
                'name' => 'Monstera Deliciosa',
                'botanical_name' => 'Monstera deliciosa',
                'slug' => 'monstera-deliciosa',
                'type' => 'plant',
                'short_description' => 'Iconic split-leaf philodendron featuring dramatic fenestrated foliage.',
                'description' => 'The Monstera Deliciosa is famous for its natural leaf-holes called fenestrations. Native to tropical rainforests of southern Mexico, it thrives indoors with moderate care and adds an instant jungle vibe to any space.',
                'base_price' => 38.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'is_published' => true,
                'requires_special_shipping' => true,
            ]
            );

            PlantAttribute::create([
                'product_id' => $monstera->id,
                'light_requirement' => 'Bright Indirect',
                'watering_frequency' => 'Weekly',
                'difficulty_level' => 'Beginner Friendly',
                'pet_friendly' => false,
                'air_purifying' => true,
                'mature_size' => 'Up to 6-8 ft indoors',
                'seasonality' => 'Active spring to early autumn',
                'growth_rate' => 'Fast',
                'ideal_temperature_range' => '18°C - 27°C',
                'humidity_requirement' => 'Average (40-60%)',
                'pot_diameter_inches' => 6.0,
                'care_instructions' => [
                    'light' => 'Filtered bright light. Avoid harsh noon direct sun to prevent leaf scorching.',
                    'watering' => 'Allow the top 2 inches of soil to dry between waterings. Reduce in winter.',
                    'soil' => 'Well-draining chunky aroid blend with bark and perlite.',
                    'pro_tip' => 'Gently wipe large foliage with a damp cloth once monthly to maximize photosynthesis.',
                ],
            ]);

            ProductVariant::create([
                'product_id' => $monstera->id,
                'sku' => 'MON-MED-6',
                'title' => 'Medium (6" Nursery Pot / ~18" Tall)',
                'price' => 38.00,
                'compare_at_price' => 45.00,
                'stock_quantity' => 25,
                'weight_grams' => 1200,
            ]);

            ProductVariant::create([
                'product_id' => $monstera->id,
                'sku' => 'MON-LRG-8',
                'title' => 'Large Specimen (8" Pot with Moss Pole / ~32" Tall)',
                'price' => 68.00,
                'compare_at_price' => 78.00,
                'stock_quantity' => 14,
                'weight_grams' => 2800,
            ]);

            // 4. Products: Snake Plant (Sansevieria Laurentii)
            $snakePlant = Product::create([
                'category_id' => $lowLightCat->id,
                'name' => 'Laurentii Snake Plant',
                'botanical_name' => 'Dracaena trifasciata',
                'slug' => 'snake-plant-laurentii',
                'type' => 'plant',
                'short_description' => 'Virtually indestructible architectural plant with golden variegated sword-like leaves.',
                'description' => 'The ultimate beginner houseplant. Known by NASA clean-air studies for converting carbon dioxide into oxygen overnight. Can withstand weeks of drought and dark rooms.',
                'base_price' => 29.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1593482892290-f54927ae1bf6?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'is_published' => true,
                'requires_special_shipping' => false,
            ]);

            PlantAttribute::create([
                'product_id' => $snakePlant->id,
                'light_requirement' => 'Low Light',
                'watering_frequency' => 'Dry out completely',
                'difficulty_level' => 'Beginner Friendly',
                'pet_friendly' => false,
                'air_purifying' => true,
                'mature_size' => 'Up to 3-4 ft indoors',
                'growth_rate' => 'Slow',
                'pot_diameter_inches' => 6.0,
                'care_instructions' => [
                    'light' => 'Tolerates low light, fluorescent lights, and direct sun.',
                    'watering' => 'Water only when potting mix is 100% dry. Once every 3 to 4 weeks.',
                    'pro_tip' => 'More Snake Plants die from overwatering than underwatering. When in doubt, wait a week!',
                ],
            ]);

            ProductVariant::create([
                'product_id' => $snakePlant->id,
                'sku' => 'SNK-6IN',
                'title' => 'Standard (6" Grow Pot)',
                'price' => 29.00,
                'stock_quantity' => 40,
                'weight_grams' => 1100,
            ]);

            // 5. Products: Golden Pothos
            $pothos = Product::create([
                'category_id' => $lowLightCat->id,
                'name' => 'Golden Pothos',
                'botanical_name' => 'Epipremnum aureum',
                'slug' => 'golden-pothos',
                'type' => 'plant',
                'short_description' => 'Lush trailing vine with heart-shaped leaves marbled in chartreuse and cream.',
                'description' => 'Fast-growing hanging plant that cascades beautifully from bookshelves and macrame hangers.',
                'base_price' => 22.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1596547609652-9cf5d8d76921?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'is_published' => true,
                'requires_special_shipping' => false,
            ]);

            PlantAttribute::create([
                'product_id' => $pothos->id,
                'light_requirement' => 'Bright Indirect',
                'watering_frequency' => 'Weekly',
                'difficulty_level' => 'Beginner Friendly',
                'pet_friendly' => false,
                'air_purifying' => true,
                'mature_size' => 'Vines reach 10+ ft',
                'growth_rate' => 'Fast',
                'pot_diameter_inches' => 5.0,
                'care_instructions' => [
                    'light' => 'Medium to bright indirect light for brightest golden variegation.',
                    'watering' => 'Water when the top 50% of soil is dry. Foliage will wilt slightly when thirsty.',
                ],
            ]);

            ProductVariant::create([
                'product_id' => $pothos->id,
                'sku' => 'POT-HNG-5',
                'title' => '5" Hanging Basket',
                'price' => 22.00,
                'stock_quantity' => 35,
                'weight_grams' => 800,
            ]);

            // 6. Products: Calathea Rattlesnake (Pet Friendly!)
            $calathea = Product::create([
                'category_id' => $indoorCat->id,
                'name' => 'Rattlesnake Calathea',
                'botanical_name' => 'Goeppertia insignis',
                'slug' => 'rattlesnake-calathea',
                'type' => 'plant',
                'short_description' => 'Non-toxic pet-safe beauty with wavy spotted foliage and deep purple undersides.',
                'description' => 'A famous "prayer plant" whose leaves fold upward at dusk. 100% safe for cats and dogs.',
                'base_price' => 32.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1620127252536-03bdfcf6d5c3?auto=format&fit=crop&w=800&q=80',
                'is_featured' => true,
                'is_published' => true,
                'requires_special_shipping' => true,
            ]);

            PlantAttribute::create([
                'product_id' => $calathea->id,
                'light_requirement' => 'Bright Indirect',
                'watering_frequency' => 'Every 2-3 days',
                'difficulty_level' => 'Moderate',
                'pet_friendly' => true,
                'air_purifying' => true,
                'mature_size' => 'Up to 24" tall',
                'growth_rate' => 'Moderate',
                'pot_diameter_inches' => 6.0,
                'humidity_requirement' => 'High (60%+)',
                'care_instructions' => [
                    'light' => 'Dappled or indirect light. Never direct sun.',
                    'watering' => 'Prefers filtered or rain water. Keep soil evenly moist, never soggy.',
                ],
            ]);

            ProductVariant::create([
                'product_id' => $calathea->id,
                'sku' => 'CAL-6IN',
                'title' => '6" Nursery Grow Pot',
                'price' => 32.00,
                'stock_quantity' => 18,
                'weight_grams' => 1000,
            ]);

            // 7. Products: Planters (Matching 6" & 8" Pots)
            $terracottaPot = Product::create([
                'category_id' => $plantersCat->id,
                'name' => 'Nordic Fluted Ceramic Planter',
                'botanical_name' => null,
                'slug' => 'nordic-fluted-ceramic-planter',
                'type' => 'planter',
                'short_description' => 'Minimalist matte glazed ceramic planter with drainage hole and matching saucer.',
                'description' => 'Crafted from high-fired ceramic, this fluted pot ensures optimal aeration and elevates any houseplant.',
                'base_price' => 24.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'is_published' => true,
            ]);

            ProductVariant::create([
                'product_id' => $terracottaPot->id,
                'sku' => 'POT-CERAMIC-6',
                'title' => '6.5" Inner Diameter (Sage Green)',
                'price' => 24.00,
                'stock_quantity' => 30,
                'weight_grams' => 1500,
            ]);

            ProductVariant::create([
                'product_id' => $terracottaPot->id,
                'sku' => 'POT-CERAMIC-8',
                'title' => '8.5" Inner Diameter (Warm Terracotta)',
                'price' => 34.00,
                'stock_quantity' => 20,
                'weight_grams' => 2400,
            ]);

            // 8. Products: Organic Aroid Soil
            $soil = Product::create([
                'category_id' => $careCat->id,
                'name' => 'Artisan Chunky Aroid Potting Mix',
                'botanical_name' => null,
                'slug' => 'artisan-chunky-aroid-mix',
                'type' => 'soil_fertilizer',
                'short_description' => 'Custom hand-blended mix of orchid bark, perlite, pumice, worm castings, and coco coir.',
                'description' => 'Guarantees root aeration and completely prevents root rot for Monsteras, Philodendrons, and Anthuriums.',
                'base_price' => 16.00,
                'primary_image_url' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=800&q=80',
                'is_featured' => false,
                'is_published' => true,
            ]);

            ProductVariant::create([
                'product_id' => $soil->id,
                'sku' => 'SOIL-5L',
                'title' => '5 Liter Resealable Bag',
                'price' => 16.00,
                'stock_quantity' => 60,
                'weight_grams' => 2100,
            ]);

            // 9. Adopted Plant in Flora's Digital Jungle
            UserPlant::create([
                'user_id' => $user->id,
                'product_id' => $monstera->id,
                'nickname' => 'Monty the Monstera',
                'adopted_at' => now()->subDays(28),
                'last_watered_at' => now()->subDays(6),
                'reminder_frequency_days' => 7,
                'notes' => 'Sprouting a massive new leaf with 4 fenestrations!',
            ]);

            UserPlant::create([
                'user_id' => $user->id,
                'product_id' => $snakePlant->id,
                'nickname' => 'Sammy the Snake',
                'adopted_at' => now()->subMonths(2),
                'last_watered_at' => now()->subDays(20),
                'reminder_frequency_days' => 21,
                'notes' => 'Standing proudly near the bedroom north-facing window.',
            ]);

            // 10. Sample Orders for Admin Fulfillment & Dispatch
            $monsteraVar = ProductVariant::where('sku', 'MON-MED-6')->first();
            $soilVar = ProductVariant::where('sku', 'SOIL-5L')->first();
            $potVar = ProductVariant::where('sku', 'POT-CERAMIC-6')->first();

            // In Transit Order
            $orderTransit = Order::create([
                'order_number' => 'ORD-BOT-8841',
                'user_id' => $user->id,
                'customer_name' => 'Flora Vance',
                'customer_email' => 'gardener@nursery.test',
                'customer_phone' => '+1 (555) 234-8901',
                'status' => 'transit',
                'subtotal' => 62.00,
                'tax_amount' => 5.27,
                'shipping_fee' => 12.00,
                'insulation_packaging_fee' => 4.50,
                'total_amount' => 83.77,
                'shipping_address' => [
                    'recipient' => 'Flora Vance',
                    'street' => '742 Evergreen Terrace',
                    'city' => 'Portland',
                    'state' => 'OR',
                    'postal_code' => '97201',
                    'country' => 'US',
                ],
                'billing_address' => [
                    'street' => '742 Evergreen Terrace',
                    'city' => 'Portland',
                    'state' => 'OR',
                    'postal_code' => '97201',
                    'country' => 'US',
                ],
                'postal_code' => '97201',
                'dispatch_weather_alert_override' => true,
                'tracking_code' => 'BOT-TRK-749102',
                'carrier_name' => 'Botanical Express Transit',
                'gift_message' => 'Happy housewarming! Keep it in indirect light.',
                'paid_at' => now()->subDays(2),
                'shipped_at' => now()->subDay(),
            ]);

            if ($monsteraVar) {
                OrderItem::create([
                    'order_id' => $orderTransit->id,
                    'product_id' => $monstera->id,
                    'product_variant_id' => $monsteraVar->id,
                    'product_name' => $monstera->name,
                    'variant_title' => $monsteraVar->title,
                    'unit_price' => 38.00,
                    'quantity' => 1,
                    'subtotal' => 38.00,
                ]);
            }

            if ($potVar) {
                OrderItem::create([
                    'order_id' => $orderTransit->id,
                    'product_id' => $potVar->product_id,
                    'product_variant_id' => $potVar->id,
                    'product_name' => 'Nordic Fluted Ceramic Planter',
                    'variant_title' => $potVar->title,
                    'unit_price' => 24.00,
                    'quantity' => 1,
                    'subtotal' => 24.00,
                ]);
            }

            // Pending Order (Requires Dispatch Review / Weather Alert)
            $orderPending = Order::create([
                'order_number' => 'ORD-BOT-8842',
                'user_id' => null,
                'customer_name' => 'Marcus Sterling',
                'customer_email' => 'marcus.sterling@example.com',
                'customer_phone' => '+1 (555) 872-3310',
                'status' => 'pending',
                'subtotal' => 38.00,
                'tax_amount' => 3.23,
                'shipping_fee' => 14.00,
                'insulation_packaging_fee' => 6.00,
                'total_amount' => 61.23,
                'shipping_address' => [
                    'recipient' => 'Marcus Sterling',
                    'street' => '104 Prairie Bluff Rd',
                    'city' => 'Minneapolis',
                    'state' => 'MN',
                    'postal_code' => '55401',
                    'country' => 'US',
                ],
                'billing_address' => [
                    'street' => '104 Prairie Bluff Rd',
                    'city' => 'Minneapolis',
                    'state' => 'MN',
                    'postal_code' => '55401',
                    'country' => 'US',
                ],
                'postal_code' => '55401',
                'dispatch_weather_alert_override' => false,
                'gift_message' => null,
                'paid_at' => now()->subHours(4),
            ]);

            if ($monsteraVar) {
                OrderItem::create([
                    'order_id' => $orderPending->id,
                    'product_id' => $monstera->id,
                    'product_variant_id' => $monsteraVar->id,
                    'product_name' => $monstera->name,
                    'variant_title' => $monsteraVar->title,
                    'unit_price' => 38.00,
                    'quantity' => 1,
                    'subtotal' => 38.00,
                ]);
            }

            // Delivered Order
            $orderDelivered = Order::create([
                'order_number' => 'ORD-BOT-8839',
                'user_id' => $user->id,
                'customer_name' => 'Flora Vance',
                'customer_email' => 'gardener@nursery.test',
                'customer_phone' => '+1 (555) 234-8901',
                'status' => 'delivered',
                'subtotal' => 32.00,
                'tax_amount' => 2.72,
                'shipping_fee' => 9.50,
                'insulation_packaging_fee' => 0.00,
                'total_amount' => 44.22,
                'shipping_address' => [
                    'recipient' => 'Flora Vance',
                    'street' => '742 Evergreen Terrace',
                    'city' => 'Portland',
                    'state' => 'OR',
                    'postal_code' => '97201',
                    'country' => 'US',
                ],
                'billing_address' => [
                    'street' => '742 Evergreen Terrace',
                    'city' => 'Portland',
                    'state' => 'OR',
                    'postal_code' => '97201',
                    'country' => 'US',
                ],
                'postal_code' => '97201',
                'dispatch_weather_alert_override' => true,
                'tracking_code' => 'BOT-TRK-748920',
                'carrier_name' => 'Botanical Express Transit',
                'paid_at' => now()->subDays(10),
                'shipped_at' => now()->subDays(8),
                'delivered_at' => now()->subDays(5),
            ]);

            if ($soilVar) {
                OrderItem::create([
                    'order_id' => $orderDelivered->id,
                    'product_id' => $soil->id,
                    'product_variant_id' => $soilVar->id,
                    'product_name' => 'Artisan Chunky Aroid Potting Mix',
                    'variant_title' => $soilVar->title,
                    'unit_price' => 16.00,
                    'quantity' => 2,
                    'subtotal' => 32.00,
                ]);
            }
        }

        // 11. Promotional Coupons & Store Discounts
        Coupon::firstOrCreate(
            ['code' => 'SPRINGBLOOM'],
            [
                'description' => '15% Off Your Entire Spring Botanical Order (Min ₹40)',
                'discount_type' => 'percentage',
                'discount_amount' => 15.00,
                'min_order_amount' => 40.00,
                'max_discount_amount' => 50.00,
                'usage_limit' => 500,
                'usage_count' => 14,
                'per_user_limit' => 1,
                'starts_at' => now()->subDays(10),
                'expires_at' => now()->addDays(60),
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'description' => '₹10 Off First Botanical Order (Min ₹35)',
                'discount_type' => 'fixed',
                'discount_amount' => 10.00,
                'min_order_amount' => 35.00,
                'max_discount_amount' => null,
                'usage_limit' => null,
                'usage_count' => 28,
                'per_user_limit' => 1,
                'starts_at' => now()->subMonths(1),
                'expires_at' => null,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'FREESHIP'],
            [
                'description' => 'Complimentary Climate-Controlled Botanical Transit (Min ₹50)',
                'discount_type' => 'free_shipping',
                'discount_amount' => 0.00,
                'min_order_amount' => 50.00,
                'max_discount_amount' => null,
                'usage_limit' => 200,
                'usage_count' => 9,
                'per_user_limit' => 2,
                'starts_at' => now()->subDays(5),
                'expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // 12. Home Page Hero Sliders
        HomeSlider::firstOrCreate(
            ['title' => 'Live Botanical Rare Plant Drop'],
            [
                'subtitle' => 'Hand-selected Variegated Monsteras, Anthuriums, and Philodendrons shipped in insulated temperature-regulated packaging.',
                'badge_text' => 'NEW ARRIVALS • SPRING 2026',
                'image_url' => 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=1600&q=85',
                'button_text' => 'Explore Rare Plants',
                'button_link' => '/catalog/indoor-plants',
                'text_align' => 'left',
                'theme' => 'dark',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        HomeSlider::firstOrCreate(
            ['title' => 'Low-Light Living Sanctuaries'],
            [
                'subtitle' => 'Architectural Dracaenas, Pothos, and Zamioculcas that purify indoor air while thriving effortlessly in gentle dim light.',
                'badge_text' => 'EASY CARE CHAMPIONS',
                'image_url' => 'https://images.unsplash.com/photo-1593482892290-f54927ae1bf6?auto=format&fit=crop&w=1600&q=85',
                'button_text' => 'Shop Low-Light Plants',
                'button_link' => '/catalog/low-light-plants',
                'text_align' => 'left',
                'theme' => 'dark',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        HomeSlider::firstOrCreate(
            ['title' => 'Artisan Ceramics & Organic Chunky Blends'],
            [
                'subtitle' => 'Give your specimens the aeration and drainage they deserve with hand-fired pots and bespoke aroid potting media.',
                'badge_text' => 'BOTANICAL CARE ESSENTIALS',
                'image_url' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=1600&q=85',
                'button_text' => 'View Pots & Nutrition',
                'button_link' => '/catalog/pots-and-planters',
                'text_align' => 'left',
                'theme' => 'dark',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );
    }
}
