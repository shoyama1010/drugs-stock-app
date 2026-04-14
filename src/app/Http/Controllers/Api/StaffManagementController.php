<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffStoreRequest;
use App\Mail\StaffAccountCreatedMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class StaffManagementController extends Controller
{
    /**
     * スタッフ一覧取得
     */
    public function index()
    {
        $staffs = User::where('role', 'staff')
            ->select([
                'id',
                'name',
                'email',
                'employee_code',
                'is_active',
                'is_pin_changed',
                'created_at',
                'updated_at',
            ])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'employee_code' => $staff->employee_code,
                    'role' => 'staff',
                    'is_active' => (bool) $staff->is_active,
                    'is_pin_changed' => (bool) $staff->is_pin_changed,
                    'created_at' => optional($staff->created_at)->format('Y-m-d H:i:s'),
                    'updated_at' => optional($staff->updated_at)->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($staffs);
    }

    /**
     * スタッフ新規登録
     */
    public function store(StaffStoreRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $employeeCode = $this->generateEmployeeCode();

            $tempPin = $this->generateTemporaryPin();

            $staff = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_code' => $employeeCode,
                'pin_hash' => Hash::make($tempPin),
                'role' => 'staff',
                'is_active' => true,
                'is_pin_changed' => false,
            ]);

            Mail::to($staff->email)->send(
                new StaffAccountCreatedMail(
                    $staff->name,
                    $employeeCode,
                    $tempPin
                )
            );

            return response()->json([
                'message' => 'スタッフを登録しました。',
                'data' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'employee_code' => $staff->employee_code,
                    'temporary_pin' => $tempPin,
                    'role' => $staff->role,
                    'is_active' => (bool) $staff->is_active,
                    'is_pin_changed' => (bool) $staff->is_pin_changed,
                    'created_at' => optional($staff->created_at)->format('Y-m-d H:i:s'),
                ]
            ], 201);
        });
    }

    /**
     * スタッフ詳細取得
     */
    public function show(User $staff)
    {
        if ($staff->role !== 'staff') {
            return response()->json([
                'message' => '対象のスタッフが存在しません。'
            ], 404);
        }

        return response()->json([
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'employee_code' => $staff->employee_code,
            'role' => $staff->role,
            'is_active' => (bool) $staff->is_active,
            'is_pin_changed' => (bool) $staff->is_pin_changed,
            'created_at' => optional($staff->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($staff->updated_at)->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * スタッフ更新
     */
    public function update(Request $request, User $staff)
    {
        if ($staff->role !== 'staff') {
            return response()->json([
                'message' => '対象のスタッフが存在しません。'
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $staff->id],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => '氏名を入力してください。',
            'name.string' => '氏名の形式が不正です。',
            'name.max' => '氏名は255文字以内で入力してください。',

            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '正しいメールアドレス形式で入力してください。',
            'email.max' => 'メールアドレスは255文字以内で入力してください。',
            'email.unique' => 'このメールアドレスは既に登録されています。',

            'is_active.required' => '有効状態を指定してください。',
            'is_active.boolean' => '有効状態の形式が不正です。',
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'message' => 'スタッフ情報を更新しました。',
            'data' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'employee_code' => $staff->employee_code,
                'role' => $staff->role,
                'is_active' => (bool) $staff->is_active,
                'is_pin_changed' => (bool) $staff->is_pin_changed,
                'updated_at' => optional($staff->updated_at)->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * スタッフ削除
     */
    public function destroy(User $staff)
    {
        if ($staff->role !== 'staff') {
            return response()->json([
                'message' => '対象のスタッフが存在しません。'
            ], 404);
        }

        $staff->delete();

        return response()->json([
            'message' => 'スタッフを削除しました。'
        ]);
    }

    /**
     * 社員番号自動採番
     */
    private function generateEmployeeCode(): string
    {
        $lastStaff = User::where('role', 'staff')
            ->whereNotNull('employee_code')
            ->orderByDesc('id')
            ->first();

        if (!$lastStaff || !$lastStaff->employee_code) {
            return 'EMP001';
        }

        $number = (int) str_replace('EMP', '', $lastStaff->employee_code);

        return 'EMP' . str_pad((string) ($number + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * 仮PIN自動生成
     */
    private function generateTemporaryPin(): string
    {
        return (string) random_int(1000, 9999);
    }
}
