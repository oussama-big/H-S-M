<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Services\PatientService;
use App\Services\DoctorService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private AuthService $authService;
    private PatientService $patientService;
    private DoctorService $doctorService;

    public function __construct(
        AuthService $authService,
        PatientService $patientService,
        DoctorService $doctorService
    ) {
        $this->authService = $authService;
        $this->patientService = $patientService;
        $this->doctorService = $doctorService;
    }

    // Affiche le formulaire de login
    public function showLogin() {
        return view('auth.login');
    }

    // Gère la connexion
    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard')->with('success', 'Bienvenue !');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    // Affiche le formulaire d'inscription
    public function showRegister() {
        return view('auth.register');
    }

    // Gère l'inscription
    public function register(Request $request) {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:PATIENT,MEDECIN',
            // Champs optionnels
            'telephone' => 'nullable',
            'specialization' => 'nullable',
        ]);

        if ($data['role'] === 'PATIENT') {
            $user = $this->patientService->registerPatient($data);
        } else {
            $user = $this->doctorService->registerDoctor($data);
        }

        Auth::login($user);
        return redirect()->route('dashboard');
    }

    // Déconnexion
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Vous avez été déconnecté.');    }
}