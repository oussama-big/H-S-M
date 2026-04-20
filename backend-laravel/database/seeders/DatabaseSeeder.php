<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Cabinet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // =============================
        // 1. CREATE CABINETS
        // =============================
        $cabinet1 = Cabinet::create([
            'nom' => 'Cabinet Médical Central',
            'adresse' => '123 Rue de la Santé, Casablanca',
            'telephone' => '+212522123456',
            'email' => 'contact@central.ma',
        ]);

        $cabinet2 = Cabinet::create([
            'nom' => 'Clinique Moderne',
            'adresse' => '456 Boulevard Hassan II, Rabat',
            'telephone' => '+212537654321',
            'email' => 'info@clinique.ma',
        ]);

        // =============================
        // 2. CREATE DOCTORS
        // =============================
        $doctorUser1 = User::create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'login' => 'dr.dupont',
            'email' => 'dr.dupont@example.com',
            'password' => Hash::make('password123'),
            'role' => 'MEDECIN',
            'telephone' => '+212600123456',
        ]);

        $doctor1 = Doctor::create([
            'id' => $doctorUser1->id,
            'specialization' => 'Médecine générale',
            'license_number' => 'DOC-2024-001',
        ]);

        $doctorUser2 = User::create([
            'nom' => 'Martin',
            'prenom' => 'Marie',
            'login' => 'dr.martin',
            'email' => 'dr.martin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'MEDECIN',
            'telephone' => '+212600234567',
        ]);

        $doctor2 = Doctor::create([
            'id' => $doctorUser2->id,
            'specialization' => 'Pédiatrie',
            'license_number' => 'DOC-2024-002',
        ]);

        // =============================
        // 3. CREATE PATIENTS
        // =============================
        $patientUser1 = User::create([
            'nom' => 'Dubois',
            'prenom' => 'Pierre',
            'login' => 'pierre.dubois',
            'email' => 'pierre.dubois@example.com',
            'password' => Hash::make('password123'),
            'role' => 'PATIENT',
            'telephone' => '+212600345678',
        ]);

        $patient1 = Patient::create([
            'id' => $patientUser1->id,
            'numDossier' => 'PAT-2024-001',
            'date_of_birth' => '1985-03-15',
            'gender' => 'M',
            'blood_type' => 'O+',
            'emergency_contact' => 'Marie Dubois - 0612345678',
        ]);

        $patientUser2 = User::create([
            'nom' => 'Leroy',
            'prenom' => 'Sophie',
            'login' => 'sophie.leroy',
            'email' => 'sophie.leroy@example.com',
            'password' => Hash::make('password123'),
            'role' => 'PATIENT',
            'telephone' => '+212600456789',
        ]);

        $patient2 = Patient::create([
            'id' => $patientUser2->id,
            'numDossier' => 'PAT-2024-002',
            'date_of_birth' => '1992-07-22',
            'gender' => 'F',
            'blood_type' => 'A+',
            'emergency_contact' => 'Jean Leroy - 0623456789',
        ]);

        // =============================
        // OUTPUT SUMMARY
        // =============================
        echo "\n✅ DATABASE SEEDED SUCCESSFULLY!\n";
        echo "================================\n";
        echo "Cabinets: 2\n";
        echo "Doctors: 2\n";
        echo "Patients: 2\n";
        echo "\n📋 TEST CREDENTIALS:\n";
        echo "Doctor 1: dr.dupont@example.com / password123\n";
        echo "Doctor 2: dr.martin@example.com / password123\n";
        echo "Patient 1: pierre.dubois@example.com / password123\n";
        echo "Patient 2: sophie.leroy@example.com / password123\n";
        echo "================================\n\n";
    }
}
