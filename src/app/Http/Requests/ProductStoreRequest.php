<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'unique:products,code'],
            'sku'         => ['required', 'string', 'unique:products,sku'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_price'  => ['required', 'integer', 'min:0'],
            'min_stock'   => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => '商品名を入力してください。',
            'name.max'             => '商品名は255文字以内で入力してください。',

            'code.required'        => '商品コードを入力してください。',
            'code.unique'          => 'この商品コードは既に使用されています。',

            'sku.required'         => 'SKUを入力してください。',
            'sku.unique'           => 'このSKUは既に使用されています。',

            'category_id.required' => 'カテゴリを選択してください。',
            'category_id.exists'   => '選択したカテゴリが存在しません。',

            'unit_price.required'  => '単価を入力してください。',
            'unit_price.integer'   => '単価は整数で入力してください。',
            'unit_price.min'       => '単価は0円以上で入力してください。',

            'min_stock.required'   => '最小在庫数を入力してください。',
            'min_stock.integer'    => '最小在庫数は整数で入力してください。',
            'min_stock.min'        => '最小在庫数は0以上で入力してください。',
        ];
    }
}
