<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ConsultationController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Create a new consultation
     * POST /consultations
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'appointment_id' => 'required|exists:appointments,id',
                'dossier_medical_id' => 'required|exists:dossier_medicals,id',
                'observations' => 'nullable|string|max:1000',
            ]);

            // Create consultation
            $consultation = $this->doctorService->createConsultation($data);

            return response()->json([
                'message' => 'Consultation created successfully',
                'data' => [
                    'id' => $consultation->id,
                    'doctor_id' => $consultation->doctor_id,
                    'appointment_id' => $consultation->appointment_id,
                    'dossier_medical_id' => $consultation->dossier_medical_id,
                    'date' => $consultation->date,
                    'observations' => $consultation->observations,
                    'created_at' => $consultation->created_at,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create consultation',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get consultation by ID with all related data
     * GET /consultations/{id}
     */
    public function show($id)
    {
        try {
            $consultation = $this->doctorService->getConsultationById($id);

            return response()->json([
                'message' => 'Consultation retrieved successfully',
                'data' => [
                    'id' => $consultation->id,
                    'doctor' => [
                        'id' => $consultation->doctor->id,
                        'nom' => $consultation->doctor->user->nom,
                        'prenom' => $consultation->doctor->user->prenom,
                        'specialization' => $consultation->doctor->specialization,
                    ],
                    'appointment' => [
                        'id' => $consultation->appointment->id,
                        'appointment_date' => $consultation->appointment->appointment_date,
                        'status' => $consultation->appointment->status,
                    ],
                    'dossier_medical' => [
                        'id' => $consultation->dossierMedical->id,
                        'patient_id' => $consultation->dossierMedical->patient_id,
                        'diagnosis' => $consultation->dossierMedical->diagnosis,
                        'treatment_plan' => $consultation->dossierMedical->treatment_plan,
                    ],
                    'date' => $consultation->date,
                    'observations' => $consultation->observations,
                    'ordonnance' => $consultation->ordonnance ? [
                        'id' => $consultation->ordonnance->id,
                        'details' => $consultation->ordonnance->details,
                        'date' => $consultation->ordonnance->date,
                    ] : null,
                    'created_at' => $consultation->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Consultation not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve consultation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update consultation
     * PUT /consultations/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $data = $request->validate([
                'observations' => 'nullable|string|max:1000',
            ]);

            $consultation = $this->doctorService->updateConsultation($id, $data);

            return response()->json([
                'message' => 'Consultation updated successfully',
                'data' => [
                    'id' => $consultation->id,
                    'observations' => $consultation->observations,
                    'updated_at' => $consultation->updated_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Consultation not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Update failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete consultation
     * DELETE /consultations/{id}
     */
    public function destroy($id)
    {
        try {
            $this->doctorService->deleteConsultation($id);

            return response()->json([
                'message' => 'Consultation deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Consultation not found'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Delete failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all consultations for a patient (via dossier medical)
     * GET /consultations/patient/{patient_id}
     */
    public function getByPatient($patientId)
    {
        try {
            // Get medical record with consultations
            $medicalRecord = $this->doctorService->getPatientMedicalRecord($patientId);

            if (!$medicalRecord) {
                return response()->json([
                    'error' => 'Medical record not found for this patient'
                ], 404);
            }

            $consultations = $medicalRecord->consultations;

            return response()->json([
                'message' => 'Patient consultations retrieved successfully',
                'count' => $consultations->count(),
                'data' => $consultations->map(function ($consultation) {
                    return [
                        'id' => $consultation->id,
                        'doctor_name' => $consultation->doctor->user->nom . ' ' . $consultation->doctor->user->prenom,
                        'date' => $consultation->date,
                        'observations' => $consultation->observations,
                        'appointment_date' => $consultation->appointment->appointment_date,
                        'has_ordonnance' => $consultation->ordonnance ? true : false,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve consultations',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
