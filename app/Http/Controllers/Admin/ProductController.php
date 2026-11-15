<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class ProductController extends Controller
{
    public function __construct(private readonly ImageService $imageService) {}

    public function index(Request $request): View
    {
        $query = Product::with(['category'])
            ->withCount('productImages');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('stock')) {
            match ($request->stock) {
                'low'  => $query->where('stock', '<=', 10)->where('stock', '>', 0),
                'out'  => $query->where('stock', 0),
                default => null,
            };
        }

        $products   = $query->orderByDesc('created_at')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category_id'       => ['required', 'exists:categories,id'],
            'sku'               => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'price'             => ['required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'stock'             => ['required', 'integer', 'min:0'],
            'description'       => ['nullable', 'string'],
            'thumbnail'         => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'gallery.*'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'is_active'         => ['nullable', 'boolean'],
            'is_featured'       => ['nullable', 'boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:500'],
            'meta_keywords'     => ['nullable', 'string', 'max:255'],
        ]);

        $data['slug']        = Str::slug($data['name']);
        if (empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }
        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['description'] = isset($data['description'])
            ? Purifier::clean($data['description'])
            : null;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->imageService->store($request->file('thumbnail'), 'products/thumbnails');
        }

        $product = Product::create($data);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $file) {
                $path = $this->imageService->store($file, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'productImages', 'reviews.user']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->load('productImages');
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'category_id'       => ['required', 'exists:categories,id'],
            'sku'               => ['nullable', 'string', 'max:100', "unique:products,sku,{$product->id}"],
            'price'             => ['required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0'],
            'stock'             => ['required', 'integer', 'min:0'],
            'description'       => ['nullable', 'string'],
            'thumbnail'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'gallery.*'         => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:2048'],
            'is_active'         => ['nullable', 'boolean'],
            'is_featured'       => ['nullable', 'boolean'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:500'],
            'meta_keywords'     => ['nullable', 'string', 'max:255'],
        ]);

        $data['slug']        = Str::slug($data['name']);
        $data['is_active']   = $request->boolean('is_active', false);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['description'] = isset($data['description'])
            ? Purifier::clean($data['description'])
            : null;

        if ($request->hasFile('thumbnail')) {
            $this->imageService->delete($product->thumbnail);
            $data['thumbnail'] = $this->imageService->store($request->file('thumbnail'), 'products/thumbnails');
        }

        $product->update($data);

        if ($request->hasFile('gallery')) {
            $existingCount = $product->productImages()->count();
            foreach ($request->file('gallery') as $index => $file) {
                $path = $this->imageService->store($file, 'products/gallery');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image'      => $path,
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Delete thumbnail
        $this->imageService->delete($product->thumbnail);

        // Delete all gallery images
        foreach ($product->productImages as $productImage) {
            $this->imageService->delete($productImage->image);
            $productImage->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyImage(Request $request, Product $product, ProductImage $productImage): RedirectResponse
    {
        // Ensure image belongs to this product
        abort_if($productImage->product_id !== $product->id, 403);

        $this->imageService->delete($productImage->image);
        $productImage->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    public function deleteImage(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        return $this->destroyImage($request, $product, $image);
    }
}
