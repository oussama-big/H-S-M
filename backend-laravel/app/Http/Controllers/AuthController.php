<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\PatientService;
use App\Services\DoctorService;

class AuthController extends Controller
{
    private AuthService $authService;
    private UserService $userService;
    private PatientService $patientService;
    private DoctorService $doctorService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        PatientService $patientService,
        DoctorService $doctorService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->patientService = $patientService;
        $this->doctorService = $doctorService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:15',
            'role' => 'required|in:PATIENT,MEDECIN',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'emergency_contact' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ]);

        $data = $request->only(
            'nom',
            'prenom',
            'email',
            'password',
            'telephone',
            'role',
            'date_of_birth',
            'gender',
            'blood_type',
            'emergency_contact',
            'specialization',
            'license_number'
        );

        if ($data['role'] === 'PATIENT') {
            $patient = $this->patientService->registerPatient($data);

            return response()->json([
                'message' => 'Patient créé avec succès',
                'data' => [
                    'patient_id' => $patient->id,
                    'numDossier' => $patient->numDossier,
                ]
            ], 201);
        }

        if ($data['role'] === 'MEDECIN') {
            $doctor = $this->doctorService->registerDoctor($data);

            return response()->json([
                'message' => 'Médecin créé avec succès',
                'data' => [
                    'doctor_id' => $doctor->id,
                    'specialization' => $doctor->specialization,
                ]
            ], 201);
        }

        return response()->json(['error' => 'Role invalide'], 400);
    }

    public function testToken(Request $request)
    {
        return response()->json([
            'token' => 'test_token_123456789_abcdef',
            'message' => 'Test token generated successfully',
            'user' => [
                'id' => 1,
                'nom' => 'Test',
                'prenom' => 'User',
                'email' => 'test@example.com',
                'role' => 'ADMIN'
            ],
            'note' => 'This is a temporary token for testing. Configure database for real authentication.'
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = $this->authService->loginUser($request->only('email', 'password'));

            if (!$user) {
                return response()->json([
                    'message' => 'Les informations d\'identification sont incorrectes.'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Connexion échouée',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logoutUser($request->user());

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
