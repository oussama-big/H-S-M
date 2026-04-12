<?php
namespace App\Services;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private PatientService $patientService;
    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function registerUser(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            return null;
        }
        return $patient = $this->patientService->registerPatient($data);
        
    }

    public function loginUser(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        return $user;
    }

        public function logoutUser(User $user)
        {
            $user->tokens()->delete();
        }
}