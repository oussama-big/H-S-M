<?php
namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminService {

    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Register a new admin
     */
    public function registerAdmin(array $data) {
        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception('Email already registered');
        }

        $user = $this->userService->createBaseUser([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'ADMIN',
            'telephone' => $data['telephone'] ?? null,
        ]);

        return Admin::create([
            'id' => $user->id,
            'department' => $data['department'] ?? null,
            'permissions' => $data['permissions'] ?? null,
        ]);
    }

    public function getAdminById($adminId) {
        $admin = Admin::with('user')->find($adminId);
        
        if (!$admin) {
            throw new ModelNotFoundException('Admin not found');
        }

        return $admin;
    }

    public function getAllAdmins() {
        return Admin::with('user')->get();
    }

    public function updateAdmin($adminId, array $data) {
        $admin = Admin::find($adminId);
        
        if (!$admin) {
            throw new ModelNotFoundException('Admin not found');
        }

        // Update user data if provided
        if (isset($data['nom']) || isset($data['prenom']) || isset($data['email'])) {
            $admin->user()->update(array_filter([
                'nom' => $data['nom'] ?? null,
                'prenom' => $data['prenom'] ?? null,
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'] ?? null,
            ], fn($value) => $value !== null));
        }

        // Update admin specific data
        $adminData = array_filter([
            'department' => $data['department'] ?? null,
            'permissions' => $data['permissions'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($adminData)) {
            $admin->update($adminData);
        }

        return $admin;
    }

    public function deleteAdmin($adminId) {
        $admin = Admin::find($adminId);
        
        if (!$admin) {
            throw new ModelNotFoundException('Admin not found');
        }

        // Delete related user
        $admin->user()->delete();
        return $admin->delete();
    }
}
