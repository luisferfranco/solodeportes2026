<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
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
        'clabe',
        'nivel',
        'nick',
        'equipo_id',
        'avatar',
        'is_active',
        'razon',
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

    public function getDisplayNameAttribute(): string {
        return $this->nick ?: $this->name;
    }

    public function getAvatarAttribute(): string {
        return "https://ui-avatars.com/api/?name={$this->name}&background=random&color=fff&size=128";
    }

    public function getSaldoAttribute(): float {
        return Transaccion::where('user_id', $this->id)
          ->where('estado', 'aprobada')
          ->sum('monto');
    }

    public function getIsAdminAttribute(): bool {
        return $this->nivel > 1;
    }

    //! RELACIONES
    public function transacciones() {
        return $this->hasMany(Transaccion::class);
    }

    public function participaciones() {
        return $this->hasMany(Participacion::class);
    }

    public function eventos() {
        return $this->hasManyThrough(
            Evento::class,
            Participacion::class,
            'user_id',      // Foreign key on Participacion table...
            'id',           // Foreign key on Evento table...
            'id',           // Local key on User table...
            'evento_id'     // Local key on Participacion table...
          )->distinct();
    }

}
