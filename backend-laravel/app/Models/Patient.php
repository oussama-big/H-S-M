<?php

namespace App\Models;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Models\DossierMedical;
use App\Models\Appointment;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use Notifiable;
    // Important car l'ID n'est pas auto-incrémenté (il vient de User)
    public $incrementing = false;
    protected $fillable = ['id', 'numDossier', 'date_of_birth', 'gender', 'blood_type', 'emergency_contact'];

    public function user() {
        return $this->belongsTo(User::class, 'id');
    }

    public function dossierMedical() {
        return $this->hasOne(DossierMedical::class);
    }

            // Dans Patient.php
        public function getNameAttribute()
        {
            return $this->user->name; 
        }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}