<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:products,code',
            'sku' => 'required|string|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'unit_price' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'category_id' => $request->category_id,
            'unit_price' => $request->unit_price,
            'min_stock' => $request->min_stock,
            'is_active' => true,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());

        return response()->json([
            'message' => '更新成功',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        \Log::info("削除実行", ['id' => $product->id]);

        $product->delete();

        return response()->json([
            'message' => '削除成功'
        ]);
    }
}
