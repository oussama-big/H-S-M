<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabinet extends Model
{
    protected $fillable = ['nom', 'adresse', 'telephone', 'email'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }
}
