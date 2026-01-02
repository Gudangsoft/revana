<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pic extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'role',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function isAuthor()
    {
        return $this->role === 'AUTOR 1';
    }

    public function isEditor()
    {
        return $this->role === 'EDITOR 1';
    }

    public function isReviewer()
    {
        return in_array($this->role, ['REVIEWER 1', 'REVIEWER 2']);
    }
}
