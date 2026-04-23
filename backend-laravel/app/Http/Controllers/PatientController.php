<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    private PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Liste de tous les patients (Vue)
     */
    public function index(Request $request)
    {
        if ($request->has('q')) {
            $patients = $this->patientService->searchPatients($request->q);
        } else {
            $patients = $this->patientService->getAllPatients();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $patients,
            ]);
        }

        return view('patients.index', compact('patients'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * API - Patient Registration
     * POST /api/patients/register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:M,F,Autre',
            'telephone' => 'required|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'Cet email est deja utilise.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'date_of_birth.required' => 'La date de naissance est obligatoire.',
            'gender.required' => 'Le genre est obligatoire.',
            'telephone.required' => 'Le telephone est obligatoire.',
        ]);

        try {
            $patient = $this->patientService->registerPatient($data);
            $token = $patient->user->createToken('patient-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Patient enregistre avec succes',
                'data' => [
                    'patient' => $patient->load('user', 'dossierMedical'),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enregistrement (Web)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'blood_type' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $patient = $this->patientService->registerPatient($data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $patient->load('user', 'dossierMedical'),
                ], 201);
            }

            return redirect()->route('patients.index')->with('success', 'Patient enregistre avec succes.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Echec de l\'enregistrement.',
                ], 400);
            }

            return back()->withInput()->with('error', 'Echec de l\'enregistrement.');
        }
    }

    /**
     * Profil detaille du patient
     */
    public function show(Request $request, $id)
    {
        $patient = $this->patientService->getPatientById($id);
        $consultations = $this->patientService->getPatientConsultationHistory($id);
        $appointments = $this->patientService->getPatientAppointments($id);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'patient' => $patient,
                    'consultations' => $consultations,
                    'appointments' => $appointments,
                ],
            ]);
        }

        return view('patients.show', compact('patient', 'consultations', 'appointments'));
    }

    /**
     * Formulaire d'edition
     */
    public function edit($id)
    {
        $patient = $this->patientService->getPatientById($id);

        return view('patients.edit', compact('patient'));
    }

    /**
     * Mise a jour
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if ($request->is('api/*') && $user && $user->role === 'PATIENT' && (int) $user->id !== (int) $id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez modifier que votre propre profil.',
            ], 403);
        }

        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $id,
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'blood_type' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
        ]);

        try {
            $patient = $this->patientService->updatePatient($id, $data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $patient->load('user', 'dossierMedical'),
                ]);
            }

            return redirect()->route('patients.show', $id)->with('success', 'Infos patient mises a jour.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de mise a jour.',
                ], 400);
            }

            return back()->with('error', 'Erreur de mise a jour.');
        }
    }

    /**
     * Suppression
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($request->is('api/*') && $user && $user->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Seul un administrateur peut supprimer un patient.',
            ], 403);
        }

        try {
            $this->patientService->deletePatient($id);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patient supprime.',
                ]);
            }

            return redirect()->route('patients.index')->with('success', 'Patient supprime.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suppression impossible.',
                ], 400);
            }

            return back()->with('error', 'Suppression impossible.');
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'ADMIN') {
            return response()->json([
                'success' => false,
                'message' => 'Acces reserve aux administrateurs.',
            ], 403);
        }

        $patient = $this->patientService->getPatientById($id);
        $patient->user->update([
            'password' => Hash::make('00000000'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Le mot de passe du patient a ete reinitialise a 00000000.',
        ]);
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        return response()->json([
            'success' => true,
            'data' => $query === '' ? [] : $this->patientService->searchPatients($query),
        ]);
    }

    public function getAppointments($id)
    {
        try {
            $appointments = $this->patientService->getPatientAppointments($id)->load('doctor.user');

            return response()->json([
                'success' => true,
                'data' => $appointments,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable.',
            ], 404);
        }
    }

    public function getConsultations($id)
    {
        try {
            $consultations = $this->patientService->getPatientConsultationHistory($id);

            return response()->json([
                'success' => true,
                'data' => $consultations,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable.',
            ], 404);
        }
    }

    public function getMedicalInfo($id)
    {
        try {
            $patient = $this->patientService->getPatientMedicalInfo($id);

            return response()->json([
                'success' => true,
                'data' => $patient,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient introuvable.',
            ], 404);
        }
    }
}
