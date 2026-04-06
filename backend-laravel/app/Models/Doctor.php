<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'specialization', 'license_number'];

    public function user() {
        return $this->belongsTo(User::class, 'id');
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

    public function consultations() {
        return $this->hasMany(Consultation::class);
    }
}