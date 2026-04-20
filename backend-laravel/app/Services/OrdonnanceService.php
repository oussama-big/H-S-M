<?php

namespace App\Services;

use App\Models\Ordonnance;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrdonnanceService
{
    // =============================
    // ORDONNANCE (PRESCRIPTION) MANAGEMENT
    // =============================

    public function createOrdonnance(array $data)
    {
        return Ordonnance::create([
            'consultation_id' => $data['consultation_id'],
            'details' => $data['details'],
            'date' => now(),
        ]);
    }

    public function getOrdonnanceById($ordonnanceId)
    {
        $ordonnance = Ordonnance::with('consultation.doctor', 'consultation.dossierMedical')
            ->find($ordonnanceId);
        
        if (!$ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        return $ordonnance;
    }

    public function getAllOrdonnances()
    {
        return Ordonnance::with('consultation')->get();
    }

    public function getOrdonnancesByConsultation($consultationId)
    {
        return Ordonnance::where('consultation_id', $consultationId)
            ->with('consultation.doctor')
            ->get();
    }

    public function getOrdonnancesByDoctor($doctorId)
    {
        return Ordonnance::whereHas('consultation', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->with('consultation')
        ->orderBy('date', 'desc')
        ->get();
    }

    public function getOrdonnancesByPatient($patientId)
    {
        return Ordonnance::whereHas('consultation.dossierMedical', function ($query) use ($patientId) {
            $query->where('patient_id', $patientId);
        })
        ->with('consultation.doctor')
        ->orderBy('date', 'desc')
        ->get();
    }

    public function getRecentOrdonnances($days = 30)
    {
        return Ordonnance::where('date', '>=', now()->subDays($days))
            ->with('consultation.doctor', 'consultation.dossierMedical')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function updateOrdonnance($ordonnanceId, array $data)
    {
        $ordonnance = Ordonnance::find($ordonnanceId);
        
        if (!$ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        $ordonnance->update($data);
        return $ordonnance;
    }

    public function deleteOrdonnance($ordonnanceId)
    {
        $ordonnance = Ordonnance::find($ordonnanceId);
        
        if (!$ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        return $ordonnance->delete();
    }
}
