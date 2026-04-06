<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ordonnance extends Model
{
    protected $fillable = ['consultation_id', 'details', 'date'];

    public function consultation() {
        return $this->belongsTo(Consultation::class);
    }
}
