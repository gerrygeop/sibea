<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'nim',
        'password',
        'role_id',
        'avatar'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasAnyRole($role): bool
    {
        return in_array($this->role->name, $role);
    }

    public function hasRole($role): bool
    {
        return $this->role->name === $role;
    }

    public function assignRole($role)
    {
        if (is_numeric($role)) {
            $roleModel = Role::find($role);
        } else {
            $roleModel = Role::where('name', $role)->first();
        }

        if (!$roleModel) {
            throw new \Exception("Role tidak ditemukan.");
        }

        $this->role_id = $roleModel->id;
        $this->save();

        return $this;
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    public function boringAvatars(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }
}
