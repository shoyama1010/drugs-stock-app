<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\StaffChangePinRequest;

// 「本人確認して入る」役割
class AuthController extends Controller
{
    /**
     * 🔐 共通ログイン（admin / staff）
     */
    public function login(Request $request)
    {
        // バリデーション
        $request->validate([
            'email' => 'nullable|email',
            'password' => 'nullable',
            'employee_code' => 'nullable',
            'pin' => 'nullable',
        ]);

        /**
         * ============================
         * 🟦 管理者ログイン
         * ============================
         */
        if ($request->email && $request->password) {

            $user = User::where('email', $request->email)
                ->where('role', 'admin')
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'ログイン失敗'], 401);
            }

            $token = $user->createToken('admin-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => 'admin',
                'user' => $user,
            ]);
        }

        /**
         * ============================
         * 🟩 スタッフログイン
         * ============================
         */
        if ($request->employee_code && $request->pin) {

            $user = User::where('employee_code', $request->employee_code)
                ->where('role', 'staff')
                ->where('is_active', true)
                ->first();

            if (!$user || !Hash::check($request->pin, $user->pin_hash)) {
                return response()->json(['message' => '認証失敗'], 401);
            }

            $token = $user->createToken('staff-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => 'staff',
                'user' => $user,

                'requires_pin_change' => !$user->is_pin_changed,
            ]);
        }

        /**
         * ❌ 入力不足
         */
        return response()->json([
            'message' => '入力が不正です'
        ], 400);
    }

    /**
     * 👤 ログインユーザー取得
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * 🔓 ログアウト
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました'
        ]);
    }

    /**
     * 🔄 スタッフPIN変更
     */
    public function changePin(StaffChangePinRequest $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'staff') {
            return response()->json([
                'message' => 'スタッフのみ利用できます。'
            ], 403);
        }

        if (!Hash::check($request->current_pin, $user->pin_hash)) {
            return response()->json([
                'message' => '現在のPINが正しくありません。'
            ], 422);
        }
        $user->update([
            'pin_hash' => Hash::make($request->new_pin),
            'is_pin_changed' => true,
        ]);

        return response()->json([
            'message' => 'PINを変更しました。'
        ], 200);
    }
}
