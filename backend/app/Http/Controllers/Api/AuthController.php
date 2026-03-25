<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;

class AuthController extends Controller
{
    // 🔐 管理者ログイン

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('role', 'admin')
            ->first();


        if (!$user || !Hash::check($request->password, $user->password))
             {

            return response()->json(['message' => 'ログイン失敗'], 401);
        }
        $token = $user->createToken('admin-token')->plainTextToken;
        return response()->json([
            'token' => $token,
            'role' => 'admin',
            'user' => $user,
        ]);
    }

    
    // 🔐 スタッフログイン
    public function staffLogin(Request $request)
    {
        $request->validate([
            'employee_code' => 'required',
            'pin' => 'required',
        ]);

        $staff = Staff::where('employee_code', $request->employee_code)
            ->where('is_active', true)
            ->first();

        if (!$staff || !Hash::check($request->pin, $staff->pin)) {
            return response()->json(['message' => '認証失敗'], 401);
        }

        $token = $staff->createToken('staff-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => 'staff',
            'user' => $staff,
        ]);
    }

    // 🔓 ログアウト
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'ログアウトしました']);
    }
}
