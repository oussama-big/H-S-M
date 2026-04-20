<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    public $incrementing = false;
    protected $fillable = ['id', 'office_number', 'assignment'];

    public function user() {
        return $this->belongsTo(User::class, 'id');
    }
}
