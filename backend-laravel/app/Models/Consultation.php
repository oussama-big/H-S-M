<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = ['doctor_id', 'appointment_id', 'dossier_medical_id', 'date', 'observations'];

    public function doctor() {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    public function dossierMedical() {
        return $this->belongsTo(DossierMedical::class);
    }

    // Une consultation peut générer une ordonnance
    public function ordonnance() {
        return $this->hasOne(Ordonnance::class);
    }
}
