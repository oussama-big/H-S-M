<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierMedical extends Model
{
    protected $fillable = ['patient_id', 'diagnosis', 'treatment_plan'];

    // Relation inverse : un dossier appartient à un patient
    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    // Un dossier contient plusieurs consultations
    public function consultations() {
        return $this->hasMany(Consultation::class);
    }
}
