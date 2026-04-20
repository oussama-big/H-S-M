<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PatientController extends Controller
{
    private PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Register a new patient
     * POST /patients/register
     */
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:M,F,Autre',
                'blood_type' => 'nullable|string|max:10',
                'emergency_contact' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $patient = $this->patientService->registerPatient($data);

            return response()->json([
                'message' => 'Patient registered successfully',
                'data' => [
                    'patient_id' => $patient->id,
                    'numDossier' => $patient->numDossier,
                    'nom' => $patient->user->nom,
                    'email' => $patient->user->email,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all patients
     * GET /patients
     */
    public function index()
    {
        try {
            $patients = $this->patientService->getAllPatients();

            return response()->json([
                'message' => 'Patients retrieved successfully',
                'count' => $patients->count(),
                'data' => $patients->map(function ($patient) {
                    return [
                        'id' => $patient->id,
                        'numDossier' => $patient->numDossier,
                        'nom' => $patient->user->nom,
                        'prenom' => $patient->user->prenom,
                        'email' => $patient->user->email,
                        'telephone' => $patient->telephone,
                        'date_of_birth' => $patient->date_of_birth,
                        'blood_type' => $patient->blood_type,
                    ];
                })
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve patients',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient by ID
     * GET /patients/{id}
     */
    public function show($id)
    {
        try {
            $patient = $this->patientService->getPatientById($id);

            return response()->json([
                'message' => 'Patient retrieved successfully',
                'data' => [
                    'id' => $patient->id,
                    'numDossier' => $patient->numDossier,
                    'nom' => $patient->user->nom,
                    'prenom' => $patient->user->prenom,
                    'email' => $patient->user->email,
                    'telephone' => $patient->telephone,
                    'date_of_birth' => $patient->date_of_birth,
                    'gender' => $patient->gender,
                    'blood_type' => $patient->blood_type,
                    'emergency_contact' => $patient->emergency_contact,
                    'appointments_count' => $patient->appointments->count(),
                    'created_at' => $patient->created_at,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update patient
     * PUT /patients/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'nom' => 'nullable|string|max:255',
                'prenom' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $id,
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:M,F,Autre',
                'blood_type' => 'nullable|string|max:10',
                'emergency_contact' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
            ]);

            $patient = $this->patientService->updatePatient($id, $data);

            return response()->json([
                'message' => 'Patient updated successfully',
                'data' => [
                    'id' => $patient->id,
                    'nom' => $patient->user->nom,
                    'email' => $patient->user->email,
                    'blood_type' => $patient->blood_type,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete patient
     * DELETE /patients/{id}
     */
    public function destroy($id)
    {
        try {
            $this->patientService->deletePatient($id);

            return response()->json([
                'message' => 'Patient deleted successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search patients
     * GET /patients/search?q=query
     */
    public function search(Request $request)
    {
        try {
            $query = $request->query('q');
            
            if (!$query) {
                return response()->json([
                    'error' => 'Search query required'
                ], 400);
            }

            $patients = $this->patientService->searchPatients($query);

            return response()->json([
                'message' => 'Patients found',
                'count' => $patients->count(),
                'data' => $patients
            ], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get patient appointments
     * GET /patients/{id}/appointments
     */
    public function getAppointments($id)
    {
        try {
            $appointments = $this->patientService->getPatientAppointments($id);

            return response()->json([
                'message' => 'Patient appointments retrieved',
                'count' => $appointments->count(),
                'data' => $appointments
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get patient consultation history
     * GET /patients/{id}/consultations
     */
    public function getConsultations($id)
    {
        try {
            $consultations = $this->patientService->getPatientConsultationHistory($id);

            return response()->json([
                'message' => 'Patient consultation history retrieved',
                'count' => $consultations->count(),
                'data' => $consultations
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get patient medical info
     * GET /patients/{id}/medical-info
     */
    public function getMedicalInfo($id)
    {
        try {
            $medicalInfo = $this->patientService->getPatientMedicalInfo($id);

            return response()->json([
                'message' => 'Patient medical info retrieved',
                'data' => [
                    'patient_id' => $medicalInfo->id,
                    'numDossier' => $medicalInfo->numDossier,
                    'blood_type' => $medicalInfo->blood_type,
                    'dossier_medical' => $medicalInfo->dossierMedical,
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Patient not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
