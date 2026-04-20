<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\AdminService;
use App\Services\DoctorService;
use App\Services\PatientService;
use App\Services\SecretaryService;
use App\Services\UserService;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // On instancie les services manuellement ou via le container
        $userService = app(UserService::class);
        $adminService = new AdminService($userService);
        $doctorService = new DoctorService($userService);
        $patientService = new PatientService($userService);
        $secretaryService = new SecretaryService($userService);

        // 1. Créer un ADMIN
        $adminService->registerAdmin([
            'nom' => 'BIZG',
            'prenom' => 'Oussama',
            'email' => 'admin@hms.com',
            'password' => 'admin123',
            'department' => 'Direction Générale'
        ]);

        // 2. Créer un MÉDECIN
        $doctorService->registerDoctor([
            'nom' => 'Alami',
            'prenom' => 'Ahmed',
            'email' => 'doctor@hms.com',
            'password' => 'doctor123',
            'specialization' => 'Cardiologie',
            'license_number' => 'LC-2026-99'
        ]);

        // 3. Créer une SECRÉTAIRE
        $secretaryService->registerSecretary([
            'nom' => 'Bennani',
            'prenom' => 'Sara',
            'email' => 'sec@hms.com',
            'password' => 'sec123',
            'office_number' => 'B-102'
        ]);

        // 4. Créer un PATIENT
        $patientService->registerPatient([
            'nom' => 'Mansouri',
            'prenom' => 'Karim',
            'email' => 'patient@hms.com',
            'password' => 'patient123',
            'date_of_birth' => '1995-05-12',
            'gender' => 'M',
            'blood_type' => 'O+'
        ]);
    }
}