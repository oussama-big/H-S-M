<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    // Important car l'ID n'est pas auto-incrémenté (il vient de User)
    public $incrementing = false;
    protected $fillable = ['id', 'numDossier', 'date_of_birth', 'gender', 'blood_type', 'emergency_contact'];

    public function user() {
        return $this->belongsTo(User::class, 'id');
    }

    public function dossierMedical() {
        return $this->hasOne(DossierMedical::class);
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}