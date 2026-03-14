<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaccion;
use App\Enums\EstadoTransaccion;
use Illuminate\Auth\Access\Response;

class TransaccionPolicy
{
    public function autorizar(User $user, Transaccion $transaccion)
    {
        if ($transaccion->estado !== EstadoTransaccion::PENDIENTE) {
            return false;
        }
        return $user->is_admin;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaccion $transaccion): bool
    {
        return $user->is_admin || $transaccion->user_id == $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaccion $transaccion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaccion $transaccion): bool
    {
        return false;
    }
}
