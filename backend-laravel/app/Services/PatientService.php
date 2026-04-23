<?php

namespace App\Services;

use App\Models\DossierMedical;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // =============================
    // 1. PATIENT REGISTRATION & MANAGEMENT
    // =============================

    public function registerPatient(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (User::where('email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Cet email est deja utilise.',
                ]);
            }

            $user = $this->userService->createBaseUser([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'PATIENT',
                'telephone' => $data['telephone'] ?? null,
            ]);

            $patient = Patient::create([
                'id' => $user->id,
                'numDossier' => 'PAT-' . strtoupper(uniqid()),
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
            ]);

            DossierMedical::firstOrCreate(
                ['patient_id' => $patient->id],
                [
                    'diagnosis' => null,
                    'treatment_plan' => null,
                ]
            );

            return $patient->load(['user', 'dossierMedical']);
        });
    }

    public function getPatientById($patientId)
    {
        $patient = Patient::with('user', 'appointments', 'dossierMedical')->find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        return $patient;
    }

    public function getAllPatients()
    {
        return Patient::with('user')->get();
    }

    public function updatePatient($patientId, array $data)
    {
        $patient = Patient::find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        if (isset($data['nom']) || isset($data['prenom']) || isset($data['email'])) {
            $patient->user()->update(array_filter([
                'nom' => $data['nom'] ?? null,
                'prenom' => $data['prenom'] ?? null,
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'] ?? null,
            ], fn($value) => $value !== null));
        }

        $patientData = array_filter([
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'emergency_contact' => $data['emergency_contact'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($patientData)) {
            $patient->update($patientData);
        }

        return $patient;
    }

    public function deletePatient($patientId)
    {
        $patient = Patient::find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        $patient->user()->delete();

        return $patient->delete();
    }

    // =============================
    // 2. PATIENT MEDICAL INFO
    // =============================

    public function getPatientMedicalInfo($patientId)
    {
        $patient = Patient::with('dossierMedical.consultations')->find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        return $patient;
    }

    public function getPatientAppointments($patientId)
    {
        $patient = Patient::find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        return $patient->appointments()->with('doctor')->get();
    }

    public function getPatientConsultationHistory($patientId)
    {
        $patient = Patient::with('dossierMedical.consultations.doctor.user')->find($patientId);

        if (!$patient) {
            throw new ModelNotFoundException('Patient not found');
        }

        return $patient->dossierMedical ? $patient->dossierMedical->consultations : [];
    }

    public function searchPatients(string $query)
    {
        return Patient::with('user')
            ->whereHas('user', function ($q) use ($query) {
                $q->where('nom', 'like', '%' . $query . '%')
                    ->orWhere('prenom', 'like', '%' . $query . '%')
                    ->orWhere('email', 'like', '%' . $query . '%');
            })
            ->orWhere('numDossier', 'like', '%' . $query . '%')
            ->get();
    }
}
