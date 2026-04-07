<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'  => ['required', 'integer', 'exists:products,id'],
            'lot_number'  => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:10000'],
            'shelf'       => ['required', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => '商品を選択してください。',
            'product_id.integer'  => '商品IDの形式が不正です。',
            'product_id.exists'   => '選択した商品が存在しません。',

            'lot_number.required' => 'ロット番号を入力してください。',
            'lot_number.string'   => 'ロット番号は文字列で入力してください。',
            'lot_number.max'      => 'ロット番号は255文字以内で入力してください。',

            'quantity.required'   => '数量を入力してください。',
            'quantity.integer'    => '数量は整数で入力してください。',
            'quantity.min'        => '数量は1以上で入力してください。',
            'quantity.max'        => '数量は10000以下で入力してください。',

            'shelf.required'      => '棚番号を入力してください。',
            'shelf.string'        => '棚番号の形式が不正です。',
            'shelf.max'           => '棚番号は255文字以内で入力してください。',

            'expiry_date.date'    => '期限日は正しい日付形式で入力してください。',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'shelf' => is_string($this->shelf)
                ? strtoupper(trim($this->shelf))
                : $this->shelf,
        ]);
    }
}
