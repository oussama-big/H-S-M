<?php

namespace App\Services;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ConsultationService
{
    // =============================
    // CONSULTATION MANAGEMENT
    // =============================

    public function createConsultation(array $data)
    {
        return Consultation::create([
            'doctor_id' => $data['doctor_id'],
            'appointment_id' => $data['appointment_id'],
            'dossier_medical_id' => $data['dossier_medical_id'],
            'date' => now(),
            'observations' => $data['observations'] ?? null,
        ]);
    }

    public function getConsultationById($consultationId)
    {
        $consultation = Consultation::with('doctor', 'appointment', 'dossierMedical', 'ordonnance')
            ->find($consultationId);
        
        if (!$consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        return $consultation;
    }

    public function getAllConsultations()
    {
        return Consultation::with('doctor', 'appointment', 'dossierMedical')->get();
    }

    public function getConsultationsByDoctor($doctorId)
    {
        return Consultation::where('doctor_id', $doctorId)
            ->with('appointment', 'dossierMedical')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getConsultationsByPatient($patientId)
    {
        return Consultation::whereHas('dossierMedical', function ($query) use ($patientId) {
            $query->where('patient_id', $patientId);
        })
        ->with('doctor', 'appointment', 'dossierMedical')
        ->orderBy('date', 'desc')
        ->get();
    }

    public function getConsultationsByDossier($dossierId)
    {
        return Consultation::where('dossier_medical_id', $dossierId)
            ->with('doctor', 'appointment')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getRecentConsultations($days = 30)
    {
        return Consultation::where('date', '>=', now()->subDays($days))
            ->with('doctor', 'dossierMedical')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function updateConsultation($consultationId, array $data)
    {
        $consultation = Consultation::find($consultationId);
        
        if (!$consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        $consultation->update($data);
        return $consultation;
    }

    public function deleteConsultation($consultationId)
    {
        $consultation = Consultation::find($consultationId);
        
        if (!$consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        return $consultation->delete();
    }
}
