<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class HotelScope implements Scope
{
    /**
     * Appliquer le scope à la requête Eloquent
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Appliquer uniquement si l'utilisateur est connecté et a un hôtel assigné
        if (Auth::check() && Auth::user()->hotel_id) {
            // Ne pas appliquer aux super-admins
            if (!Auth::user()->hasRole('super-admin')) {
                $builder->where($model->getTable() . '.hotel_id', Auth::user()->hotel_id);
            }
        }
    }
}
