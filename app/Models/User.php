<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarAttribute(): string
    {
        return "https://ui-avatars.com/api/?name={$this->name}&background=random&color=fff&size=128";
    }

    public function getSaldoAttribute(): float
    {
        return Transaccion::where('user_id', $this->id)
          ->where('estado', 'aprobada')
          ->sum('monto');
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->nivel > 1;
    }

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class);
    }
}
