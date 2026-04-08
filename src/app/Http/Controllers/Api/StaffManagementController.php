<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StaffStoreRequest;
use App\Mail\StaffAccountCreatedMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

// 「adminがスタッフ情報を管理する」役割
class StaffManagementController extends Controller
{
    public function store(StaffStoreRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $lastStaff = User::where('role', 'staff')
                ->whereNotNull('employee_code')
                ->orderByDesc('id')
                ->first();

            if (!$lastStaff || !$lastStaff->employee_code) {
                $employeeCode = 'EMP001';
            } else {
                $number = (int) str_replace('EMP', '', $lastStaff->employee_code);
                $employeeCode = 'EMP' . str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
            }

            $tempPin = (string) random_int(1000, 9999);

            $staff = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_code' => $employeeCode,
                'pin' => Hash::make($tempPin), // pinをハッシュ化する場合
                // 'pin' => $tempPin, // もし今の設計が平文保存なら一旦こちら
                'role' => 'staff',
            ]);

            Mail::to($staff->email)->send(
                new StaffAccountCreatedMail(
                    name: $staff->name,
                    employeeCode: $employeeCode,
                    tempPin: $tempPin
                )
            );

            return response()->json([
                'message' => 'スタッフを登録し、メールを送信しました。',
                'data' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'employee_code' => $staff->employee_code,
                    'role' => $staff->role,
                ]
            ], 201);
        });
    }

    public function index()
    {
        $staffs = User::where('role', 'staff')->get();

        return response()->json($staffs);
    }
}
