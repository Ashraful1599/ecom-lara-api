<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Image;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with([
            'variants.attributes',
            'variants.images',  // Variant images
            'images',           // Product images
            'categories',
            'tags'
        ])->get();

        return response()->json($products, 200);
    }



    public function store(Request $request)
    {
        \Log::info('all response', $request->all());

        try {
            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'salePrice' => 'nullable|numeric',
                'sku' => 'nullable|string',
                'stock' => 'nullable|integer',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'productType' => 'required|string',
                'productStatus' => 'required|string',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'variants' => 'nullable|array',
                'variants.*.attributes' => 'nullable|array',
                'variants.*.price' => 'nullable|numeric',
                'variants.*.salePrice' => 'nullable|numeric',
                'variants.*.sku' => 'nullable|string',
                'variants.*.stock' => 'nullable|integer',
                'variants.*.image' => 'nullable|file|image|max:2048',
                'featuredImage' => 'nullable|file|image|max:2048',
                'gallery' => 'nullable|array',
                'gallery.*' => 'nullable|file|image|max:2048',
            ]);

            // Generate slug based on name if not provided
            $slug = $validated['slug'] ?? Str::slug($validated['name']);
            $slug = $this->generateUniqueSlug($slug);

            // Generate SKU if not provided and ensure it's unique
            $sku = $validated['sku'] ?? strtoupper(Str::random(8)); // Generate a random SKU if not provided
            $sku = $this->generateUniqueSku($sku);

            // Create the product
            $product = Product::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'sale_price' => $validated['salePrice'] ?? null,
                'sku' => $sku,
                'stock' => $validated['stock'] ?? 0,
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'product_type' => $validated['productType'],
                'product_status' => $validated['productStatus'],
            ]);

            // Attach categories and tags if they exist
            if (!empty($validated['categories'])) {
                $product->categories()->attach($validated['categories']);
            }

            if (!empty($validated['tags'])) {
                $product->tags()->sync($validated['tags']);
            }

            // Handle the featured image upload
            if ($request->hasFile('featuredImage')) {
                $featuredImagePath = $request->file('featuredImage')->store('images', 'public');
                $product->images()->create([
                    'image_path' => $featuredImagePath,
                    'is_featured' => true,
                ]);
            }

            // Handle gallery image uploads
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $galleryImage) {
                    $galleryImagePath = $galleryImage->store('images', 'public');
                    $product->images()->create([
                        'image_path' => $galleryImagePath,
                        'is_featured' => false,
                    ]);
                }
            }

            // Check if there are variants and create them
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $index => $variantData) {
                    // Generate a unique SKU for the variant if not provided
                    $variantSku = $variantData['sku'] ?? strtoupper(Str::random(8));
                    $variantSku = $this->generateUniqueSku($variantSku);

                    // Create the variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'price' => $variantData['price'] ?? 0,
                        'sale_price' => $variantData['salePrice'] ?? null,
                        'sku' => $variantSku,
                        'stock' => $variantData['stock'] ?? 0,
                    ]);

                    // Attach attributes to the variant if they exist
                    $attributeValueIds = [];
                    if (!empty($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attributeName => $value) {
                            $attribute = Attribute::where('name', $attributeName)->first();
                            if ($attribute) {
                                $attributeValue = AttributeValue::where('value', $value)
                                    ->where('attribute_id', $attribute->id)
                                    ->first();
                                if ($attributeValue) {
                                    $attributeValueIds[] = $attributeValue->id;
                                }
                            }
                        }
                    }

                    if (!empty($attributeValueIds)) {
                        $variant->attributes()->attach($attributeValueIds);
                    }

                    // Handle variant image upload if provided (using index-based access)
                    if ($request->hasFile("variants.{$index}.image")) {
                        $variantImagePath = $request->file("variants.{$index}.image")->store('images', 'public');
                        $variant->images()->create(['image_path' => $variantImagePath]);
                    }
                }
            }


            return response()->json($product->load('variants.attributes', 'images'), 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            // Check if it's a duplicate entry error
            if ($e->getCode() === '23000') { // 23000 is the SQL error code for integrity constraint violation
                return response()->json([
                    'message' => 'Duplicate entry detected. Please check the SKU, slug, or other unique fields.',
                    'error' => $e->getMessage()
                ], 409); // 409 Conflict is a suitable HTTP status code for this type of error
            }

            return response()->json([
                'message' => 'An error occurred while creating the product.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error while creating product: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique slug by appending a number if the slug already exists.
     *
     * @param string $slug
     * @return string
     */
    private function generateUniqueSlug($slug)
    {
        $originalSlug = $slug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Generate a unique SKU by appending a number if the SKU already exists.
     *
     * @param string $sku
     * @return string
     */
    private function generateUniqueSku($sku)
    {
        $originalSku = $sku;
        $count = 1;

        while (Product::where('sku', $sku)->exists() || ProductVariant::where('sku', $sku)->exists()) {
            $sku = "{$originalSku}-{$count}";
            $count++;
        }

        return $sku;
    }




    public function update(Request $request, $id)
    {
        \Log::info('Request data:', $request->all());
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'salePrice' => 'nullable|numeric',
                'sku' => 'nullable|string',
                'stock' => 'nullable|integer',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'productType' => 'required|string',
                'productStatus' => 'required|string',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:categories,id',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'variants' => 'nullable|array',
                'variants.*.attributes' => 'nullable|array',
                'variants.*.price' => 'nullable|numeric',
                'variants.*.id' => 'nullable|numeric',
                'variants.*.salePrice' => 'nullable|numeric',
                'variants.*.sku' => 'nullable|string',
                'variants.*.stock' => 'nullable|integer',
                'variants.*.image' => 'nullable|file|image|max:2048',
                'featuredImage' => 'nullable|file|image|max:2048',
                'gallery' => 'nullable|array',
                'gallery.*' => 'nullable|file|image|max:2048',
                'existingGalleryImages' => 'nullable|array',
                'removedImages' => 'nullable|array',
            ]);

            $product = Product::findOrFail($id);

            // Update product info
            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'sale_price' => $validated['salePrice'] ?? null,
                'sku' => $validated['sku'] ?? $product->sku,
                'stock' => $validated['stock'] ?? 0,
                'slug' => $validated['slug'] ?? Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'product_type' => $validated['productType'],
                'product_status' => $validated['productStatus'],
            ]);

            $product->categories()->sync($validated['categories'] ?? []);
            $product->tags()->sync($validated['tags'] ?? []);

            // Handle the featured image
            if ($request->hasFile('featuredImage')) {
                $featuredImagePath = $request->file('featuredImage')->store('images', 'public');
                $product->images()->where('is_featured', true)->delete();
                $product->images()->create([
                    'image_path' => $featuredImagePath,
                    'is_featured' => true,
                ]);
            }

            // Manage gallery images (remove/add)
            if (!empty($validated['removedImages'])) {
                foreach ($validated['removedImages'] as $imagePath) {
                    $product->images()->where('image_path', $imagePath)->delete();
                }
            }

            $existingGalleryImages = $validated['existingGalleryImages'] ?? [];
            $product->images()->where('is_featured', false)
                ->whereNotIn('image_path', $existingGalleryImages)
                ->delete();

            foreach ($existingGalleryImages as $existingImagePath) {
                $product->images()->firstOrCreate([
                    'image_path' => $existingImagePath,
                    'is_featured' => false,
                ]);
            }

            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $galleryImage) {
                    $galleryImagePath = $galleryImage->store('images', 'public');
                    $product->images()->create([
                        'image_path' => $galleryImagePath,
                        'is_featured' => false,
                    ]);
                }
            }


            // Handle variants and their images
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $index => $variantData) {


                    // Check if a variant ID exists in the request
                    $variantId = $variantData['id'] ?? null;

                    \Log::info('$variantData',$variantData);

                    // Update the variant if it exists; otherwise, create it
                    $variant = ProductVariant::updateOrCreate(
                        [
                            'id' => $variantId, // Use ID if available, otherwise match by SKU and product_id
                            'product_id' => $product->id
                        ],
                        [
                            'sku' => $variantData['sku'] ?? strtoupper(Str::random(8)),
                            'price' => $variantData['price'] ?? 0,
                            'sale_price' => $variantData['salePrice'] ?? null,
                            'stock' => $variantData['stock'] ?? 0,
                        ]
                    );

                    // Sync variant attributes
                    $attributeValueIds = [];
                    foreach ($variantData['attributes'] ?? [] as $attributeName => $value) {
                        $attribute = Attribute::where('name', $attributeName)->first();
                        if ($attribute) {
                            $attributeValue = AttributeValue::where('value', $value)
                                ->where('attribute_id', $attribute->id)
                                ->first();
                            if ($attributeValue) {
                                $attributeValueIds[] = $attributeValue->id;
                            }
                        }
                    }
                    $variant->attributes()->sync($attributeValueIds);

                    // Handle variant image
                    if ($request->hasFile("variants.{$index}.image")) {
                        // Delete existing images if needed
                        $variant->images()->delete();
                        $variantImagePath = $request->file("variants.{$index}.image")->store('images', 'public');
                        $variant->images()->create(['image_path' => $variantImagePath]);
                    }
                }
            }


            return response()->json($product->load('variants.attributes', 'images'), 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Duplicate entry detected. Please check the SKU, slug, or other unique fields.',
                    'error' => $e->getMessage()
                ], 409);
            }

            return response()->json([
                'message' => 'An error occurred while updating the product.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Error while updating product: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the product.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function show($id)
    {
        // Fetch a single product with its variants, attributes, and images
        $product = Product::with([
            'variants.attributes',
            'variants.image',  // Variant image
            'images',           // Product images
            'categories',
            'tags'])->findOrFail($id);

        return response()->json($product, 200);
    }


    public  function  destroy( $id)
    {
     $product = Product::destroy($id);

     return response()->json($product, 200);

    }

    public function bulkDeleteProducts(Request $request)
    {
        $ids = $request->input('ids'); // Get array of IDs from request
        if (is_array($ids) && count($ids) > 0) {
            $deletedCount = Product::destroy($ids); // Delete multiple products with an array of IDs
            return response()->json(['deleted' => $deletedCount], 200);
        } else {
            return response()->json(['error' => 'No valid IDs provided'], 400);
        }
    }




}
