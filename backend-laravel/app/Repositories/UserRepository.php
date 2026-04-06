<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository {
    // Les fonctions de recherche seront codées au Sprint 2
    public function getAll() {
        return User::all();
    }

    public function findById($id) {
        return User::findOrFail($id);
    }

    public function getByRole($role) {
        return User::where('role', $role)->get();
    }
}