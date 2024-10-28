<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a demo user
        User::factory()->create([
            'name' => 'Ash',
            'email' => 'smashrafulcse@gmail.com',
            'password' => bcrypt('12345678')
        ]);

//        // Create Categories
//        $category = Category::create([
//            'name' => 'Clothing',
//            'slug' => 'clothing'
//        ]);
//
//        // Create Attributes
//        $size = Attribute::create(['name' => 'Size']);
//        $color = Attribute::create(['name' => 'Color']);
//
//        // Create Attribute Values for Size
//        $small = AttributeValue::create(['attribute_id' => $size->id, 'value' => 'Small']);
//        $medium = AttributeValue::create(['attribute_id' => $size->id, 'value' => 'Medium']);
//        $large = AttributeValue::create(['attribute_id' => $size->id, 'value' => 'Large']);
//
//        // Create Attribute Values for Color
//        $blue = AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Blue']);
//        $white = AttributeValue::create(['attribute_id' => $color->id, 'value' => 'White']);
//        $black = AttributeValue::create(['attribute_id' => $color->id, 'value' => 'Black']);
//
//        // Create Products
//        $product = Product::create([
//            'name' => 'T-Shirt',
//            'slug' => 't-shirt',
//            'description' => 'A comfortable cotton t-shirt.',
//            'price' => 10.00,
//            'stock' => 100,
//            'category_id' => $category->id,
//        ]);
//
//        // Create Product Variants
//        $variants = [
//            ['size' => $small, 'color' => $blue, 'price' => 12.00, 'stock' => 50],
//            ['size' => $small, 'color' => $white, 'price' => 13.00, 'stock' => 30],
//            ['size' => $medium, 'color' => $blue, 'price' => 14.00, 'stock' => 60],
//            ['size' => $medium, 'color' => $white, 'price' => 15.00, 'stock' => 40],
//            ['size' => $large, 'color' => $black, 'price' => 16.00, 'stock' => 20],
//        ];
//
//        foreach ($variants as $variant) {
//            $productVariant = ProductVariant::create([
//                'product_id' => $product->id,
//                'price' => $variant['price'],
//                'stock' => $variant['stock'],
//            ]);
//
//            // Attach attributes to each variant
//            $productVariant->attributes()->attach([
//                $variant['size']->id,
//                $variant['color']->id,
//            ]);
//        }
    }
}
