<?php

namespace App\Repositories;

use App\Models\Doctor;

class DoctorRepository {
    public function getBySpecialization($specialty) {
        return Doctor::with('user')
            ->where('specialization', $specialty)
            ->get();
    }

    public function findById($id) {
        return Doctor::findOrFail($id);
    }
    public function getAllWithUser() {
        return Doctor::with('user')->get();
    }

}