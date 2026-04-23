<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\DoctorService;
use App\Services\PatientService;
use Illuminate\Http\Request;
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

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
            return $this->loginAPI($credentials);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('backoffice.dashboard'))->with('success', 'la connexion est reussit');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects.'])->onlyInput('email');
    }

    private function loginAPI(array $credentials)
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'la connexion est reussit',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect',
        ], 401);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:PATIENT,MEDECIN',
            'telephone' => 'nullable',
            'specialization' => 'nullable',
        ]);

        $user = $data['role'] === 'PATIENT'
            ? $this->patientService->registerPatient($data)->user
            : $this->doctorService->registerDoctor($data)->user;

        Auth::login($user);

        return redirect()->route('backoffice.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Vous avez ete deconnecte.');
    }
}
