<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->admin('super_admin')->create([
            'email' => 'admin@nursery.test',
        ]);

        $this->category = Category::create([
            'name' => 'Indoor Plants',
            'slug' => 'indoor-plants',
            'description' => 'Lush tropical houseplants',
        ]);
    }

    public function test_admin_can_create_product_by_uploading_primary_image_from_local_system(): void
    {
        $primaryFile = UploadedFile::fake()->image('fiddle-leaf-fig.jpg', 800, 800);
        $galleryFile1 = UploadedFile::fake()->image('leaf-detail.png', 600, 600);
        $galleryFile2 = UploadedFile::fake()->image('pot-angle.webp', 600, 600);

        $payload = [
            'name' => 'Ficus Lyrata',
            'botanical_name' => 'Ficus lyrata Warb.',
            'category_id' => $this->category->id,
            'type' => 'plant',
            'base_price' => 54.00,
            'primary_image' => $primaryFile,
            'gallery_images' => [$galleryFile1, $galleryFile2],
            'short_description' => 'Dramatic violin-shaped foliage.',
            'description' => 'A statement houseplant beloved for broad architectural leaves.',
            'is_published' => '1',
            'variants' => [
                [
                    'sku' => 'FLF-6IN',
                    'title' => '6" Ceramic Grower Pot',
                    'price' => 54.00,
                    'stock_quantity' => 15,
                    'low_stock_threshold' => 4,
                    'weight_grams' => 1200,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/admin/products', $payload);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $product = Product::where('slug', 'ficus-lyrata')->first();
        $this->assertNotNull($product);

        // Verify primary image is saved to public storage disk
        $this->assertStringContainsString('products/primary', $product->primary_image_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($product->primary_image_url, PHP_URL_PATH)));

        // Verify gallery images are saved to public storage disk
        $this->assertCount(2, $product->gallery_images);
        foreach ($product->gallery_images as $galleryUrl) {
            $this->assertStringContainsString('products/gallery', $galleryUrl);
            Storage::disk('public')->assertExists(str_replace('/storage/', '', parse_url($galleryUrl, PHP_URL_PATH)));
        }
    }

    public function test_product_creation_fails_when_no_primary_image_or_url_provided(): void
    {
        $payload = [
            'name' => 'Snake Plant',
            'category_id' => $this->category->id,
            'type' => 'plant',
            'base_price' => 28.00,
            'description' => 'Hardy air purifying botanical specimen.',
            'variants' => [
                [
                    'sku' => 'SP-4IN',
                    'title' => '4" Standard',
                    'price' => 28.00,
                    'stock_quantity' => 20,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post('/admin/products', $payload);

        $response->assertSessionHasErrors(['primary_image']);
        $this->assertDatabaseMissing('products', ['name' => 'Snake Plant']);
    }

    public function test_admin_can_update_product_and_replace_primary_image_from_local_system(): void
    {
        $oldFile = UploadedFile::fake()->image('old-photo.jpg');
        $initialPath = $oldFile->store('products/primary', 'public');

        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Monstera Deliciosa',
            'slug' => 'monstera-deliciosa',
            'type' => 'plant',
            'description' => 'Swiss cheese plant.',
            'base_price' => 42.00,
            'primary_image_url' => Storage::url($initialPath),
            'gallery_images' => ['https://images.unsplash.com/photo-gallery-1'],
            'is_published' => true,
        ]);

        $newReplacementFile = UploadedFile::fake()->image('monstera-new-leaf.jpg', 1000, 1000);
        $newGalleryFile = UploadedFile::fake()->image('monstera-macro.jpg', 800, 800);

        $payload = [
            'name' => 'Monstera Deliciosa Variegata',
            'category_id' => $this->category->id,
            'type' => 'plant',
            'base_price' => 65.00,
            'description' => 'Rare variegated Swiss cheese plant.',
            'primary_image' => $newReplacementFile,
            'existing_gallery_images' => ['https://images.unsplash.com/photo-gallery-1'],
            'gallery_images' => [$newGalleryFile],
            'variants' => [
                [
                    'sku' => 'MON-VAR-01',
                    'title' => 'Variegated 6" Pot',
                    'price' => 65.00,
                    'stock_quantity' => 5,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put("/admin/products/{$product->id}", $payload);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $product->refresh();
        $this->assertEquals('Monstera Deliciosa Variegata', $product->name);

        // Verify primary image was updated to new file
        $newStoredPath = str_replace('/storage/', '', parse_url($product->primary_image_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($newStoredPath);

        // Verify existing gallery image was kept and new gallery image was appended
        $this->assertCount(2, $product->gallery_images);
        $this->assertContains('https://images.unsplash.com/photo-gallery-1', $product->gallery_images);

        $newGalleryPath = str_replace('/storage/', '', parse_url($product->gallery_images[1], PHP_URL_PATH));
        Storage::disk('public')->assertExists($newGalleryPath);
    }
}
