<?php
namespace App\Services;

use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SecretaryService {

    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Register a new secretary
     */
    public function registerSecretary(array $data) {
        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception('Email already registered');
        }

        $user = $this->userService->createBaseUser([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'SECRETAIRE',
            'telephone' => $data['telephone'] ?? null,
        ]);

        return Secretary::create([
            'id' => $user->id,
            'office_number' => $data['office_number'] ?? null,
            'assignment' => $data['assignment'] ?? null,
        ]);
    }

    public function getSecretaryById($secretaryId) {
        $secretary = Secretary::with('user')->find($secretaryId);
        
        if (!$secretary) {
            throw new ModelNotFoundException('Secretary not found');
        }

        return $secretary;
    }

    public function getAllSecretaries() {
        return Secretary::with('user')->get();
    }

    public function updateSecretary($secretaryId, array $data) {
        $secretary = Secretary::find($secretaryId);
        
        if (!$secretary) {
            throw new ModelNotFoundException('Secretary not found');
        }

        // Update user data if provided
        if (isset($data['nom']) || isset($data['prenom']) || isset($data['email'])) {
            $secretary->user()->update(array_filter([
                'nom' => $data['nom'] ?? null,
                'prenom' => $data['prenom'] ?? null,
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'] ?? null,
            ], fn($value) => $value !== null));
        }

        // Update secretary specific data
        $secretaryData = array_filter([
            'office_number' => $data['office_number'] ?? null,
            'assignment' => $data['assignment'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($secretaryData)) {
            $secretary->update($secretaryData);
        }

        return $secretary;
    }

    public function deleteSecretary($secretaryId) {
        $secretary = Secretary::find($secretaryId);
        
        if (!$secretary) {
            throw new ModelNotFoundException('Secretary not found');
        }

        // Delete related user
        $secretary->user()->delete();
        return $secretary->delete();
    }
}
