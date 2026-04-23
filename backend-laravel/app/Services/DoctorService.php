<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\DossierMedical;
use App\Models\Ordonnance;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorService
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function registerDoctor(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception('Email already registered');
        }

        $user = $this->userService->createBaseUser([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'login' => $data['login'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'MEDECIN',
            'telephone' => $data['telephone'] ?? null,
        ]);

        return Doctor::create([
            'id' => $user->id,
            'license_number' => $data['license_number'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'cabinet_id' => $data['cabinet_id'] ?? null,
        ]);
    }

    public function getDoctorById($doctorId)
    {
        $doctor = Doctor::with(['user', 'appointments', 'consultations', 'cabinet'])->find($doctorId);

        if (! $doctor) {
            throw new ModelNotFoundException('Doctor not found');
        }

        return $doctor;
    }

    public function getAllDoctors()
    {
        return Doctor::with(['user', 'cabinet'])->get();
    }

    public function updateDoctor($doctorId, array $data)
    {
        $doctor = Doctor::find($doctorId);

        if (! $doctor) {
            throw new ModelNotFoundException('Doctor not found');
        }

        if (isset($data['nom']) || isset($data['prenom']) || isset($data['email']) || isset($data['login'])) {
            $doctor->user()->update(array_filter([
                'nom' => $data['nom'] ?? null,
                'prenom' => $data['prenom'] ?? null,
                'login' => $data['login'] ?? null,
                'email' => $data['email'] ?? null,
                'telephone' => $data['telephone'] ?? null,
            ], fn ($value) => $value !== null));
        }

        $doctorData = array_filter([
            'specialization' => $data['specialization'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'cabinet_id' => $data['cabinet_id'] ?? null,
        ], fn ($value) => $value !== null);

        if (! empty($doctorData)) {
            $doctor->update($doctorData);
        }

        return $doctor;
    }

    public function deleteDoctor($doctorId)
    {
        $doctor = Doctor::find($doctorId);

        if (! $doctor) {
            throw new ModelNotFoundException('Doctor not found');
        }

        $doctor->user()->delete();

        return $doctor->delete();
    }

    public function createAppointment(array $data)
    {
        $this->validateDoctorExists($data['doctor_id']);

        return Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'status' => 'PREVU',
            'reason' => $data['reason'] ?? null,
        ]);
    }

    public function getAppointmentById($appointmentId)
    {
        $appointment = Appointment::with('patient', 'doctor', 'consultation')->find($appointmentId);

        if (! $appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        return $appointment;
    }

    public function getAppointmentsByDoctor($doctorId)
    {
        $this->validateDoctorExists($doctorId);

        return Appointment::where('doctor_id', $doctorId)->with('patient')->get();
    }

    public function getAppointmentsByPatient($patientId)
    {
        return Appointment::where('patient_id', $patientId)->with('doctor')->get();
    }

    public function updateAppointment($appointmentId, array $data)
    {
        $appointment = Appointment::find($appointmentId);

        if (! $appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        $appointment->update($data);

        return $appointment;
    }

    public function deleteAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);

        if (! $appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        return $appointment->delete();
    }

    public function createConsultation(array $data)
    {
        $this->validateDoctorExists($data['doctor_id']);

        return Consultation::create([
            'doctor_id' => $data['doctor_id'],
            'appointment_id' => $data['appointment_id'],
            'dossier_medical_id' => $data['dossier_medical_id'],
            'date' => now(),
            'observations' => $data['observations'] ?? null,
        ]);
    }

    public function getConsultationById($consultationId)
    {
        $consultation = Consultation::with('doctor', 'appointment', 'dossierMedical', 'ordonnance')->find($consultationId);

        if (! $consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        return $consultation;
    }

    public function updateConsultation($consultationId, array $data)
    {
        $consultation = Consultation::find($consultationId);

        if (! $consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        $consultation->update($data);

        return $consultation;
    }

    public function deleteConsultation($consultationId)
    {
        $consultation = Consultation::find($consultationId);

        if (! $consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        return $consultation->delete();
    }

    public function createOrdonnance(array $data)
    {
        return Ordonnance::create([
            'consultation_id' => $data['consultation_id'],
            'details' => $data['details'],
            'date' => now(),
        ]);
    }

    public function getOrdonnanceById($ordonnanceId)
    {
        $ordonnance = Ordonnance::with('consultation')->find($ordonnanceId);

        if (! $ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        return $ordonnance;
    }

    public function updateOrdonnance($ordonnanceId, array $data)
    {
        $ordonnance = Ordonnance::find($ordonnanceId);

        if (! $ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        $ordonnance->update($data);

        return $ordonnance;
    }

    public function deleteOrdonnance($ordonnanceId)
    {
        $ordonnance = Ordonnance::find($ordonnanceId);

        if (! $ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        return $ordonnance->delete();
    }

    public function getPatientMedicalRecord($patientId)
    {
        return DossierMedical::where('patient_id', $patientId)
            ->with('consultations', 'patient')
            ->first();
    }

    public function updateDossierMedical($dossierId, array $data)
    {
        $dossier = DossierMedical::find($dossierId);

        if (! $dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        $dossier->update($data);

        return $dossier;
    }

    private function validateDoctorExists($doctorId)
    {
        if (! Doctor::find($doctorId)) {
            throw new ModelNotFoundException('Doctor not found');
        }
    }
}
