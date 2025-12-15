<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\HotelScope;

class Group extends Model
{
    /**
     * Le scope "booted" du modèle.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new HotelScope());
    }
}
