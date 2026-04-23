<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['patient_id', 'doctor_id', 'appointment_date', 'status', 'reason' ];

    protected $casts = [
    'appointment_date' => 'datetime',
];

    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() {
        return $this->belongsTo(Doctor::class);
    }

    public function consultation() {
        return $this->hasOne(Consultation::class);
    }
}
