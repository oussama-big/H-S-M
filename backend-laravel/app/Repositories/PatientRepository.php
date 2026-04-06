<?php
namespace App\Repositories;

use App\Models\Patient;

class PatientRepository {
    public function getAllWithUser() {
        return Patient::with('user')->get();
    }

    public function findByNumDossier($num) {
        return Patient::where('numDossier', $num)->first();
    }

    public function findById($id) {
        return Patient::findOrFail($id);
    }
}