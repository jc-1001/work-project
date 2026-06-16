<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage   = min((int) $request->input('per_page', 15), 100);
        $paginated = Product::with('category')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->has('is_active') && $request->is_active !== null,
                fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json([
            'data'         => $paginated->items(),
            'total'        => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page'    => $paginated->lastPage(),
        ]);
    }

    public function frontIndex(Request $request)
    {
        $perPage   = min((int) $request->input('per_page', 12), 100);
        $paginated = Product::with('category')
            ->where('is_active', 1)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->paginate($perPage);

        return response()->json([
            'data'         => $paginated->items(),
            'total'        => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page'     => $paginated->perPage(),
            'last_page'    => $paginated->lastPage(),
        ]);
    }

    public function frontShow($id)
    {
        return response()->json([
            'product' => Product::with('productImages')->where('is_active', 1)->findOrFail($id),
        ]);
    }

    public function categories()
    {
        return response()->json([
            'categories' => Category::all(),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'product' => Product::with(['category', 'productImages'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $oldImagePath = $product->image;

        $newImagePath = null;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('products', 'public');
        }

        try {
            $product->update([
                'name'        => $request->name,
                'category_id' => $request->category_id,
                'price'       => $request->price,
                'stock'       => $request->stock,
                'description' => $request->description,
                'image'       => $newImagePath ?? $product->image,
                'is_active'   => $request->input('is_active', true),
            ]);
        } catch (\Throwable $e) {
            if ($newImagePath) Storage::disk('public')->delete($newImagePath);
            throw $e;
        }

        if ($newImagePath && $oldImagePath) {
            try {
                Storage::disk('public')->delete($oldImagePath);
            } catch (\Throwable $e) {
                Log::warning("舊圖刪除失敗: {$oldImagePath} - {$e->getMessage()}");
            }
        }

        return response()->json([
            'product' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'required|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $uploadedPaths = [];
        foreach ($request->file('images') ?? [] as $file) {
            $uploadedPaths[] = $file->store('products', 'public');
        }

        try {
            $product = DB::transaction(function () use ($request, $uploadedPaths) {
                $coverPath  = $uploadedPaths[0] ?? null;
                $extraPaths = array_slice($uploadedPaths, 1);

                $product = Product::create([
                    'name'        => $request->name,
                    'category_id' => $request->category_id,
                    'price'       => $request->price,
                    'stock'       => $request->stock,
                    'description' => $request->description,
                    'image'       => $coverPath,
                    'is_active'   => $request->input('is_active', true),
                ]);

                foreach ($extraPaths as $i => $path) {
                    $product->productImages()->create(['image_path' => $path, 'sort_order' => $i]);
                }

                return $product;
            });
        } catch (\Throwable $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return response()->json(['product' => $product->load('productImages')], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'price'          => 'required|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'image'          => 'nullable|image|max:2048',
            'new_images'     => 'nullable|array|max:5',
            'new_images.*'   => 'image|max:2048',
            'is_active'      => 'boolean',
        ]);

        $oldCoverPath = $product->image;

        $newCoverPath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $newExtraPaths = [];
        foreach ($request->file('new_images') ?? [] as $file) {
            $newExtraPaths[] = $file->store('products', 'public');
        }

        try {
            DB::transaction(function () use ($request, $product, $newCoverPath, $newExtraPaths) {
                $product->update([
                    'name'        => $request->name,
                    'category_id' => $request->category_id,
                    'price'       => $request->price,
                    'stock'       => $request->stock,
                    'description' => $request->description,
                    'image'       => $newCoverPath ?? $product->image,
                    'is_active'   => $request->input('is_active', true),
                ]);

                $existingCount = $product->productImages()->count();
                foreach ($newExtraPaths as $i => $path) {
                    $product->productImages()->create([
                        'image_path'  => $path,
                        'sort_order'  => $existingCount + $i,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            if ($newCoverPath) Storage::disk('public')->delete($newCoverPath);
            foreach ($newExtraPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        if ($newCoverPath && $oldCoverPath) {
            try {
                Storage::disk('public')->delete($oldCoverPath);
            } catch (\Throwable $e) {
                Log::warning("舊封面刪除失敗: {$oldCoverPath} - {$e->getMessage()}");
            }
        }

        return response()->json(['product' => $product->fresh()->load('productImages')]);
    }

    public function deleteImage($productId, $imageId)
    {
        $product = Product::findOrFail($productId);
        $image   = $product->productImages()->findOrFail($imageId);

        try {
            Storage::disk('public')->delete($image->image_path);
        } catch (\Throwable $e) {
            Log::warning("附圖刪除失敗: {$image->image_path} - {$e->getMessage()}");
        }

        $image->delete();

        return response()->json(['message' => '圖片已刪除']);
    }

    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'       => 'required|array|max:100',
            'ids.*'     => 'integer|exists:products,id',
            'is_active' => 'required|boolean',
        ]);

        Product::whereIn('id', $request->ids)
            ->update(['is_active' => $request->boolean('is_active')]);

        return response()->json(['message' => '批次更新成功']);
    }
}
