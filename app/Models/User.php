<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Evento;
use App\Models\Participacion;
use App\Models\Transaccion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable, SoftDeletes;

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
    return $this->nick ?? $this->name;
  }
  public function getAvatarUrlAttribute(): string {
    return $this->avatar ? asset('storage/' . $this->avatar) : "https://ui-avatars.com/api/?name={$this->name}&background=random&color=fff&size=128";
  }
  public function getSaldoAttribute(): float {
    // Saldo aprobado
    return Transaccion::where('user_id', $this->id)
      ->where('estado', 'aprobada')
      ->sum('monto');
  }
  public function getRetirosPendientesAttribute(): float {
    // Saldo pendiente de retiro/depósito
    return (Transaccion::where('user_id', $this->id)
      ->where('monto', '<', 0)
      ->where('estado', 'pendiente')
      ->sum('monto')) * -1;
  }
  public function getIsAdminAttribute(): bool {
    return $this->nivel > 1;
  }
  public function getAdministradorEventosAttribute() {
    return $this->eventosAdministrados->count() > 0;
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
  public function eventosAdministrados() {
    return $this->belongsToMany(Evento::class, 'administradores_eventos');
  }
  public function invtaciones_enviadas() {
    return $this->hasMany(Invitacion::class, 'invitante_id');
  }
  public function invitaciones_recibidas() {
    return $this->hasMany(Invitacion::class, 'invitado_id');
  }
}
