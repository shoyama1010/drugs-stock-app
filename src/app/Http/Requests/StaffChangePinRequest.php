<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffChangePinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'digits:4'],
            'new_pin' => ['required', 'digits:4', 'different:current_pin'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_pin.required' => '現在のPINを入力してください。',
            'current_pin.digits' => '現在のPINは4桁の数字で入力してください。',

            'new_pin.required' => '新しいPINを入力してください。',
            'new_pin.digits' => '新しいPINは4桁の数字で入力してください。',
            'new_pin.different' => '新しいPINは現在のPINと異なるものを入力してください。',

            'new_pin_confirmation.required' => '新しいPINの確認を入力してください。',
            'new_pin_confirmation.same' => '新しいPINと確認用PINが一致しません。',
        ];
    }
}
