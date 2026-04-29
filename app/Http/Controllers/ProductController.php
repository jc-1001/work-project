<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 取得所有商品（後台，含下架）
    public function index()
    {
        return Product::with('category')->get();
    }

    // 取得上架商品列表（前台，支援分類篩選與分頁）
    public function frontIndex(Request $request)
    {
        // 限制上限為100，避免全部撈光資料
        $perPage = min((int) $request->input('per_page', 12), 100);

        return Product::with('category')
            ->where('is_active', 1)
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->paginate($perPage);
    }

    // 取得單一上架商品（前台）
    public function frontShow($id)
    {
        return Product::where('is_active', 1)->findOrFail($id);
    }

    // 取得所有分類
    public function categories()
    {
        return Category::all();
    }

    // 取得單一商品（後台，不過濾 is_active）
    public function show($id)
    {
        return Product::with('category')->findOrFail($id);
    }

    // 更新商品
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

        // 在 update 前先存 $oldImagePath
        // 先記錄舊圖路徑，update() 後會同步為新值
        $oldImagePath = $product->image;

        // 先上傳新圖片（暫存路徑），確保 DB 更新成功後才刪除舊檔
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
            // DB 失敗時清除剛上傳的新圖，避免孤立檔案
            if ($newImagePath) Storage::disk('public')->delete($newImagePath);
            throw $e;
        }

        // DB 成功後才刪除舊圖，刪除失敗僅記錄 warning，不影響回應
        if ($newImagePath && $oldImagePath) {
            try {
                Storage::disk('public')->delete($oldImagePath);
            } catch (\Throwable $e) {
                Log::warning("舊圖刪除失敗: {$oldImagePath} - {$e->getMessage()}");
            }
        }

        return response()->json($product);
    }

    // 新增商品
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'description' => $request->description,
            'image'       => $imagePath,
            'is_active'   => $request->input('is_active', true),
        ]);

        return response()->json($product, 201);
    }
}
