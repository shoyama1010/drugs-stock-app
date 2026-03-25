<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;

// class Staff extends Model
class Staff extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'staffs'; // ← これ追加

    protected $fillable = [
        'name',
        'employee_code',
        'pin',
        'is_active',
    ];

    protected $hidden = [
        'pin',
        // 'remember_token',
    ];

    protected $casts = [
        // 'pin' => 'hashed',
    ];
}
