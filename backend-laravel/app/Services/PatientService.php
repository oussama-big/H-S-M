<?php
namespace App\Services;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PatientService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function registerPatient(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            return null;
        }
        $user = $this->userService->createBaseUser([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'PATIENT',
            'telephone' => $data['telephone'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),

        ]);

        return Patient::create([
            'id' => $user->id,
            'numDossier' => 'PAT-' . strtoupper(uniqid()),
            'telephone' => $data['telephone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}