<?php

namespace App\Models;

use App\Assembly\Core\Model;

class User extends Model
{
    public static string $table = 'users';
    public static array $fillable = ['first_name', 'last_name', 'email'];
    public static array $hidden = ['password', 'created_at', 'updated_at'];
    public static array $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    public static array $appends = ['full_name'];
    public static bool $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}