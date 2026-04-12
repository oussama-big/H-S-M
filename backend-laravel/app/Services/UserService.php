<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createBaseUser(array $data)
    {
        return User::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'telephone'=> $data['telephone'] ?? null,
        ]);
    }
}