<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'  => ['required', 'integer', 'exists:products,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'reason'      => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'  => '商品を選択してください。',
            'product_id.integer'   => '商品IDの形式が不正です。',
            'product_id.exists'    => '選択した商品が存在しません。',

            'location_id.required' => '棚を選択してください。',
            'location_id.integer'  => '棚IDの形式が不正です。',
            'location_id.exists'   => '選択した棚が存在しません。',

            'quantity.required'    => '数量を入力してください。',
            'quantity.integer'     => '数量は整数で入力してください。',
            'quantity.min'         => '数量は1以上で入力してください。',
            'quantity.max'        => '数量は10000以下で入力してください。',


            'reason.required'      => '理由を入力してください。',
            'reason.string'        => '理由の形式が不正です。',
            'reason.max'           => '理由は255文字以内で入力してください。',
        ];
    }
}
