<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'ログイン失敗'
            ], 401);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
        
        // $credentials = $request->only('email', 'password');

        // // 認証チェック
        // if (!Auth::attempt($credentials)) {
        //     return response()->json([
        //         'message' => 'ログイン失敗'
        //     ], 401);
        // }


        // $user = Auth::user();

        // // トークン発行（Sanctum）
        // $token = $user->createToken('admin-token')->plainTextToken;

        // return response()->json([
        //     'user' => $user,
        //     'token' => $token
        // ]);
    }
}
