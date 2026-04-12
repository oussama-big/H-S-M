<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient; // N'oublie pas l'import
use App\Models\Doctor;  // N'oublie pas l'import
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\PatientService;

class AuthController extends Controller
{
    private $authService;
    private $userService;
    private $patientService;


    public function __construct(AuthService $authService, UserService $userService , PatientService $patientService)
    {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->patientService = $patientService;
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



        ]);
        $patient = $this->patientService->registerPatient($request->only(
            'nom', 'prenom', 'email', 'password', 'telephone', 'role' , 'date_of_birth', 'gender', 'blood_type', 'emergency_contact'
        ));
        return response()->json(['message' => 'Patient créé avec succès'], 201);
    }



    public function login(Request $request)
    {
        if (ob_get_contents()) ob_clean();
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = $this->authService->loginUser($request->only('email', 'password'));
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification sont incorrectes.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user 
        ]);
    }

    

    public function logout(Request $request)
    {
        $this->authService->logoutUser($request->user());

        return response()->json(['message' => 'Déconnexion réussie']);
    }
}