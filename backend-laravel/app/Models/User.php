<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['nom', 'prenom', 'login', 'telephone', 'email', 'password', 'role'];

    // Relation vers le profil Patient
    public function patient() {
        return $this->hasOne(Patient::class, 'id');
    }

    // Relation vers le profil Docteur
    public function doctor() {
        return $this->hasOne(Doctor::class, 'id');
    }

    // Relation vers le profil Secretaire
    public function secretary() {
        return $this->hasOne(Secretary::class, 'id');
    }

    // Notifications
    public function notifications() {
        return $this->hasMany(Notification::class);
    }
}
