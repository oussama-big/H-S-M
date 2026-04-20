<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function createBaseUser(array $data)
    {
        $login = $data['login'] ?? Str::slug(explode('@', $data['email'])[0]);
        $originalLogin = $login;
        $counter = 1;

        while (User::where('login', $login)->exists()) {
            $login = $originalLogin . '-' . $counter++;
        }

        return User::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'login'    => $login,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'telephone'=> $data['telephone'] ?? null,
        ]);
    }
}