<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DossierMedicalController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Get patient's medical record
     * GET /dossiers-medicaux/patient/{patient_id}
     */
    public function getByPatient($patientId)
    {
        try {
            $dossier = $this->doctorService->getPatientMedicalRecord($patientId);

            if (!$dossier) {
                return response()->json([
                    'error' => 'Medical record not found for this patient'
                ], 404);
            }

            return response()->json([
                'message' => 'Medical record retrieved successfully',
                'data' => [
                    'id' => $dossier->id,
                    'patient' => [
                        'id' => $dossier->patient->id,
                        'nom' => $dossier->patient->user->nom,
                        'prenom' => $dossier->patient->user->prenom,
                        'email' => $dossier->patient->user->email,
                    ],
                    'diagnosis' => $dossier->diagnosis,
                    'treatment_plan' => $dossier->treatment_plan,
                    'consultations_count' => $dossier->consultations->count(),
                    'consultations' => $dossier->consultations->map(function ($consultation) {
                        return [
                            'id' => $consultation->id,
                            'doctor_name' => $consultation->doctor->user->nom . ' ' . $consultation->doctor->user->prenom,
                            'date' => $consultation->date,
                            'observations' => $consultation->observations,
                            'has_ordonnance' => $consultation->ordonnance ? true : false,
                        ];
                    }),
                    'created_at' => $dossier->created_at,
                    'updated_at' => $dossier->updated_at,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve medical record',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update medical record (diagnosis and treatment plan)
     * PUT /dossiers-medicaux/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $data = $request->validate([
                'diagnosis' => 'nullable|string|max:1000',
                'treatment_plan' => 'nullable|string|max:2000',
            ]);

            $dossier = $this->doctorService->updateDossierMedical($id, $data);

            return response()->json([
                'message' => 'Medical record updated successfully',
                'data' => [
                    'id' => $dossier->id,
                    'patient_id' => $dossier->patient_id,
                    'diagnosis' => $dossier->diagnosis,
                    'treatment_plan' => $dossier->treatment_plan,
                    'updated_at' => $dossier->updated_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Medical record not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get detailed medical record by ID
     * GET /dossiers-medicaux/{id}
     */
    public function show($id)
    {
        try {
            $dossier = \App\Models\DossierMedical::with('consultations', 'patient')
                ->find($id);

            if (!$dossier) {
                return response()->json([
                    'error' => 'Medical record not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Medical record retrieved successfully',
                'data' => [
                    'id' => $dossier->id,
                    'patient' => [
                        'id' => $dossier->patient->id,
                        'nom' => $dossier->patient->user->nom,
                        'prenom' => $dossier->patient->user->prenom,
                        'email' => $dossier->patient->user->email,
                        'telephone' => $dossier->patient->user->telephone,
                    ],
                    'diagnosis' => $dossier->diagnosis,
                    'treatment_plan' => $dossier->treatment_plan,
                    'consultations_count' => $dossier->consultations->count(),
                    'last_consultation' => $dossier->consultations->last() ? [
                        'date' => $dossier->consultations->last()->date,
                        'doctor' => $dossier->consultations->last()->doctor->user->nom,
                        'observations' => $dossier->consultations->last()->observations,
                    ] : null,
                    'created_at' => $dossier->created_at,
                    'updated_at' => $dossier->updated_at,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve medical record',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary of patient's medical history
     * GET /dossiers-medicaux/patient/{patient_id}/summary
     */
    public function getSummary($patientId)
    {
        try {
            $dossier = $this->doctorService->getPatientMedicalRecord($patientId);

            if (!$dossier) {
                return response()->json([
                    'error' => 'Medical record not found'
                ], 404);
            }

            $totalOrdonnances = $dossier->consultations->sum(function ($consultation) {
                return $consultation->ordonnance ? 1 : 0;
            });

            return response()->json([
                'message' => 'Medical summary retrieved successfully',
                'data' => [
                    'patient_name' => $dossier->patient->user->nom . ' ' . $dossier->patient->user->prenom,
                    'diagnosis' => $dossier->diagnosis,
                    'treatment_plan' => $dossier->treatment_plan,
                    'total_consultations' => $dossier->consultations->count(),
                    'total_ordonnances' => $totalOrdonnances,
                    'last_consultation_date' => $dossier->consultations->last() ? $dossier->consultations->last()->date : null,
                    'record_created_at' => $dossier->created_at,
                    'last_updated_at' => $dossier->updated_at,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
