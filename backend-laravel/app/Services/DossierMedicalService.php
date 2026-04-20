<?php

namespace App\Services;

use App\Models\DossierMedical;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DossierMedicalService
{
    // =============================
    // DOSSIER MEDICAL (MEDICAL RECORD) MANAGEMENT
    // =============================

    public function createDossierMedical(array $data)
    {
        return DossierMedical::create([
            'patient_id' => $data['patient_id'],
            'diagnosis' => $data['diagnosis'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
        ]);
    }

    public function getDossierMedicalById($dossierId)
    {
        $dossier = DossierMedical::with('consultations.doctor', 'patient.user')
            ->find($dossierId);
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        return $dossier;
    }

    public function getAllDossiers()
    {
        return DossierMedical::with('patient.user')->get();
    }

    public function getDossierByPatient($patientId)
    {
        $dossier = DossierMedical::where('patient_id', $patientId)
            ->with('consultations.doctor', 'patient.user')
            ->first();
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found for this patient');
        }

        return $dossier;
    }

    public function updateDossierMedical($dossierId, array $data)
    {
        $dossier = DossierMedical::find($dossierId);
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        $dossierData = array_filter([
            'diagnosis' => $data['diagnosis'] ?? null,
            'treatment_plan' => $data['treatment_plan'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($dossierData)) {
            $dossier->update($dossierData);
        }

        return $dossier;
    }

    public function getDossierSummary($patientId)
    {
        $dossier = DossierMedical::where('patient_id', $patientId)
            ->with('consultations', 'patient.user')
            ->first();
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        $totalOrdonnances = $dossier->consultations->sum(function ($consultation) {
            return $consultation->ordonnance ? 1 : 0;
        });

        return [
            'patient_name' => $dossier->patient->user->nom . ' ' . $dossier->patient->user->prenom,
            'patient_id' => $dossier->patient->id,
            'diagnosis' => $dossier->diagnosis,
            'treatment_plan' => $dossier->treatment_plan,
            'total_consultations' => $dossier->consultations->count(),
            'total_ordonnances' => $totalOrdonnances,
            'last_consultation_date' => $dossier->consultations->last() ? $dossier->consultations->last()->date : null,
            'created_at' => $dossier->created_at,
            'updated_at' => $dossier->updated_at,
        ];
    }

    public function getDossierHistory($patientId)
    {
        return DossierMedical::where('patient_id', $patientId)
            ->with('consultations.doctor.user', 'consultations.ordonnance')
            ->first();
    }

    public function deleteDossierMedical($dossierId)
    {
        $dossier = DossierMedical::find($dossierId);
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        return $dossier->delete();
    }
}
