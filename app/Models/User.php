<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
	/** @use HasFactory<\\Database\\Factories\\UserFactory> */
	use HasFactory, Notifiable, HasRoles;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'hotel_id',
		'name',
		'email',
		'password',
	];

	public function hotel()
	{
		return $this->belongsTo(Hotel::class);
	}

	/**
	 * Relation avec les sessions utilisateur
	 */
	public function sessions()
	{
		return $this->hasMany(UserSession::class);
	}

	/**
	 * Relation avec les pré-réservations validées par cet utilisateur
	 */
	public function validatedReservations()
	{
		return $this->hasMany(Reservation::class, 'validated_by');
	}

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
}


