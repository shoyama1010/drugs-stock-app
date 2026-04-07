<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;

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
     * Store a newly created resource in storage.
     */

    public function store(ProductStoreRequest $request)
    {
        $validated = $request->validated();

        $product = Product::create([
            'name'        => $validated['name'],
            'code'        => $validated['code'],
            'sku'         => $validated['sku'],
            'category_id' => $validated['category_id'],
            'unit_price'  => $validated['unit_price'],
            'min_stock'   => $validated['min_stock'],
            'is_active'   => true,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'message' => '更新成功',
            'data' => $product,
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
}
