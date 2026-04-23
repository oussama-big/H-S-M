<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Cabinet;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Secretary;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function getDashboardData(?int $viewerId = null): array
    {
        $doctors = Doctor::with(['user', 'cabinet'])
            ->withCount(['appointments', 'consultations'])
            ->get()
            ->sortBy(fn (Doctor $doctor) => strtolower($this->doctorName($doctor)))
            ->values();

        $secretaries = Secretary::with('user')
            ->get()
            ->sortBy(fn (Secretary $secretary) => strtolower($this->secretaryName($secretary)))
            ->values();

        $cabinets = Cabinet::withCount('doctors')
            ->orderBy('nom')
            ->get();

        $patients = Patient::with([
            'user',
            'appointments.doctor.user',
            'appointments.doctor.cabinet',
            'dossierMedical.consultations.doctor.user',
            'dossierMedical.consultations.appointment.doctor.user',
            'dossierMedical.consultations.appointment.doctor.cabinet',
            'dossierMedical.consultations.ordonnance',
        ])
            ->withCount('appointments')
            ->get()
            ->sortByDesc('created_at')
            ->values();

        $appointmentsTotal = Appointment::count();
        $todayAppointments = Appointment::whereDate('appointment_date', today())->count();
        $cancelledAppointments = Appointment::where('status', 'ANNULE')->count();
        $completedAppointments = Appointment::where('status', 'PASSE')->count();
        $consultationsToday = Consultation::whereDate('date', today())->count();
        $consultationsTotal = Consultation::count();
        $uniquePatientsPerDoctor = Appointment::query()
            ->select('doctor_id', DB::raw('COUNT(DISTINCT patient_id) as unique_patients'))
            ->groupBy('doctor_id')
            ->pluck('unique_patients', 'doctor_id');

        $appointmentsByDoctor = $doctors->map(function (Doctor $doctor) {
            return [
                'name' => $this->doctorName($doctor),
                'count' => (int) $doctor->appointments_count,
            ];
        })->sortByDesc('count')->values()->all();

        $consultationsByDoctor = $doctors->map(function (Doctor $doctor) {
            return [
                'name' => $this->doctorName($doctor),
                'count' => (int) $doctor->consultations_count,
            ];
        })->sortByDesc('count')->values()->all();

        $patientsFollowedByDoctor = $doctors->map(function (Doctor $doctor) use ($uniquePatientsPerDoctor) {
            return [
                'name' => $this->doctorName($doctor),
                'count' => (int) ($uniquePatientsPerDoctor[$doctor->id] ?? 0),
            ];
        })->sortByDesc('count')->values()->all();

        $doctorsByCabinet = $this->buildDoctorsByCabinet($cabinets, $doctors);
        $consultationFlow = $this->buildConsultationFlow()->all();
        $mostActiveDoctor = $doctors
            ->sortByDesc('appointments_count')
            ->sortByDesc('consultations_count')
            ->first();

        $formattedPatients = $patients->map(function (Patient $patient) {
            return $this->formatPatient($patient);
        })->values()->all();

        $formattedDoctors = $doctors->map(function (Doctor $doctor) use ($uniquePatientsPerDoctor) {
            return [
                'id' => $doctor->id,
                'nom' => $doctor->user->nom ?? '',
                'prenom' => $doctor->user->prenom ?? '',
                'full_name' => $this->doctorName($doctor),
                'login' => $doctor->user->login ?? '',
                'telephone' => $doctor->user->telephone ?? '',
                'email' => $doctor->user->email ?? '',
                'specialization' => $doctor->specialization,
                'license_number' => $doctor->license_number,
                'cabinet_id' => $doctor->cabinet_id,
                'cabinet' => $doctor->cabinet?->nom ?? 'Cabinet principal',
                'appointments_count' => (int) $doctor->appointments_count,
                'consultations_count' => (int) $doctor->consultations_count,
                'unique_patients_count' => (int) ($uniquePatientsPerDoctor[$doctor->id] ?? 0),
            ];
        })->values()->all();

        $formattedSecretaries = $secretaries->map(function (Secretary $secretary) {
            return [
                'id' => $secretary->id,
                'nom' => $secretary->user->nom ?? '',
                'prenom' => $secretary->user->prenom ?? '',
                'full_name' => $this->secretaryName($secretary),
                'login' => $secretary->user->login ?? '',
                'telephone' => $secretary->user->telephone ?? '',
                'email' => $secretary->user->email ?? '',
                'office_number' => $secretary->office_number,
                'assignment' => $secretary->assignment,
            ];
        })->values()->all();

        $formattedCabinets = $cabinets->map(function (Cabinet $cabinet) {
            return [
                'id' => $cabinet->id,
                'nom' => $cabinet->nom,
                'adresse' => $cabinet->adresse,
                'telephone' => $cabinet->telephone,
                'email' => $cabinet->email,
                'doctors_count' => (int) $cabinet->doctors_count,
            ];
        })->values()->all();

        $notifications = $viewerId
            ? Notification::where('user_id', $viewerId)->orderByDesc('date')->take(6)->get()
            : collect();

        return [
            'summary' => [
                'total_patients' => count($formattedPatients),
                'total_doctors' => count($formattedDoctors),
                'total_secretaries' => count($formattedSecretaries),
                'total_cabinets' => count($formattedCabinets),
                'appointments_total' => $appointmentsTotal,
                'today_appointments' => $todayAppointments,
                'consultations_total' => $consultationsTotal,
                'consultations_today' => $consultationsToday,
                'cancellation_rate' => $this->rate($cancelledAppointments, $appointmentsTotal),
                'completion_rate' => $this->rate($completedAppointments, $appointmentsTotal),
            ],
            'advanced' => [
                'doctors_by_cabinet' => $doctorsByCabinet,
                'appointments_by_doctor' => $appointmentsByDoctor,
                'consultations_by_doctor' => $consultationsByDoctor,
                'patients_followed_by_doctor' => $patientsFollowedByDoctor,
                'consultation_flow' => $consultationFlow,
                'most_active_doctor' => $mostActiveDoctor ? [
                    'name' => $this->doctorName($mostActiveDoctor),
                    'appointments' => (int) $mostActiveDoctor->appointments_count,
                    'consultations' => (int) $mostActiveDoctor->consultations_count,
                ] : null,
                'cancelled_appointments' => $cancelledAppointments,
                'completed_appointments' => $completedAppointments,
            ],
            'patients' => $formattedPatients,
            'doctors' => $formattedDoctors,
            'secretaries' => $formattedSecretaries,
            'cabinets' => $formattedCabinets,
            'notifications' => [
                'total' => $viewerId ? Notification::where('user_id', $viewerId)->count() : 0,
                'unread' => $viewerId ? Notification::where('user_id', $viewerId)->where('is_read', false)->count() : 0,
                'items' => $notifications->map(function (Notification $notification) {
                    return [
                        'id' => $notification->id,
                        'content' => $notification->contenu,
                        'date' => $notification->date ? Carbon::parse((string) $notification->date)->toDateTimeString() : null,
                        'is_read' => (bool) $notification->is_read,
                    ];
                })->values()->all(),
            ],
        ];
    }

    private function buildDoctorsByCabinet(Collection $cabinets, Collection $doctors): array
    {
        $rows = $cabinets->map(function (Cabinet $cabinet) {
            return [
                'name' => $cabinet->nom,
                'count' => (int) $cabinet->doctors_count,
            ];
        })->values();

        $unassignedDoctors = $doctors->whereNull('cabinet_id')->count();

        if ($unassignedDoctors > 0 || $rows->isEmpty()) {
            $rows->push([
                'name' => 'Sans cabinet',
                'count' => (int) $unassignedDoctors,
            ]);
        }

        return $rows->all();
    }

    private function buildConsultationFlow(): Collection
    {
        $startDate = Carbon::today()->subDays(6);
        $rows = Consultation::query()
            ->select('date', DB::raw('COUNT(*) as total'))
            ->whereDate('date', '>=', $startDate->toDateString())
            ->groupBy('date')
            ->pluck('total', 'date');

        return collect(range(0, 6))->map(function (int $offset) use ($startDate, $rows) {
            $day = $startDate->copy()->addDays($offset);
            $key = $day->toDateString();

            return [
                'date' => $day->format('d/m'),
                'full_date' => $key,
                'count' => (int) ($rows[$key] ?? 0),
            ];
        });
    }

    private function formatPatient(Patient $patient): array
    {
        $latestAppointment = $patient->appointments->sortByDesc('appointment_date')->first();
        $dossierMedical = $patient->dossierMedical;
        $consultations = $dossierMedical?->consultations?->sortByDesc('date') ?? collect();
        $latestConsultation = $consultations->first();
        $doctor = $latestAppointment?->doctor ?? $latestConsultation?->doctor;
        $cabinet = $doctor?->cabinet;
        $status = $this->buildPatientStatus($latestAppointment, $consultations->count());
        $timeline = $this->buildPatientTimeline($patient, $consultations);
        $prescription = $latestConsultation?->ordonnance?->details ?: 'Aucune ordonnance recente';
        $diagnosis = $dossierMedical?->diagnosis ?: 'Diagnostic non renseigne';
        $tests = $dossierMedical?->treatment_plan ?: 'Aucun plan de traitement enregistre';
        $hospital = $cabinet?->nom ?: 'Hopital Medicare';
        $admissionDate = optional($patient->created_at)->format('d/m/Y') ?: '--';

        return [
            'id' => $patient->id,
            'nom' => $patient->user->nom ?? '',
            'prenom' => $patient->user->prenom ?? '',
            'email' => $patient->user->email ?? '',
            'telephone' => $patient->user->telephone ?? '',
            'date_of_birth' => $patient->date_of_birth,
            'gender' => $patient->gender,
            'blood_type' => $patient->blood_type,
            'emergency_contact' => $patient->emergency_contact,
            'patient_id' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'name' => trim(($patient->user->prenom ?? '') . ' ' . ($patient->user->nom ?? '')),
            'hospital' => $hospital,
            'admission_date' => $admissionDate,
            'status_label' => $status['label'],
            'status_class' => $status['class'],
            'patient_type' => $consultations->count() > 0 ? 'inpatients' : 'outpatients',
            'has_billing' => $consultations->count() > 0,
            'has_appointments' => $patient->appointments_count > 0,
            'details' => [
                'title' => trim(($patient->user->prenom ?? '') . ' ' . ($patient->user->nom ?? '')),
                'patient_id' => $patient->numDossier ?: 'PAT-' . $patient->id,
                'hospital' => $hospital,
                'id_number' => (string) $patient->id,
                'email' => $patient->user->email ?: 'Non renseigne',
                'phone' => $patient->user->telephone ?: 'Non renseigne',
                'admission_date' => $admissionDate,
                'status_label' => $status['label'],
                'status_class' => $status['class'],
                'doctor' => $doctor ? $this->doctorName($doctor) : 'Aucun medecin assigne',
                'ward' => $cabinet?->nom ?: 'Consultation externe',
                'diagnosis' => $diagnosis,
                'prescription' => $prescription,
                'tests' => $tests,
                'timeline' => $timeline,
            ],
        ];
    }

    private function buildPatientStatus(?Appointment $latestAppointment, int $consultationsCount): array
    {
        if ($latestAppointment?->status === 'PREVU') {
            return ['label' => 'Suivi actif', 'class' => 'status-admitted'];
        }

        if ($latestAppointment?->status === 'ANNULE') {
            return ['label' => 'En attente', 'class' => 'status-pending'];
        }

        if ($consultationsCount > 0 || $latestAppointment?->status === 'PASSE') {
            return ['label' => 'Dossier traite', 'class' => 'status-discharged'];
        }

        return ['label' => 'Nouveau patient', 'class' => 'status-pending'];
    }

    private function buildPatientTimeline(Patient $patient, Collection $consultations): array
    {
        $timeline = $consultations
            ->sortBy('date')
            ->map(function (Consultation $consultation) {
                $doctorName = $consultation->doctor ? $this->doctorName($consultation->doctor) : 'Medecin non renseigne';
                $details = $consultation->observations ?: ($consultation->ordonnance?->details ?: 'Consultation realisee et archivee.');

                return [
                    'date' => Carbon::parse($consultation->date)->format('d/m/Y'),
                    'title' => 'Consultation',
                    'description' => $doctorName . ' - ' . $details,
                ];
            })
            ->values();

        if ($timeline->isEmpty()) {
            $timeline->push([
                'date' => optional($patient->created_at)->format('d/m/Y') ?: Carbon::today()->format('d/m/Y'),
                'title' => 'Inscription',
                'description' => 'Patient enregistre dans le systeme H-S-M.',
            ]);
        }

        return $timeline->all();
    }

    private function doctorName(Doctor $doctor): string
    {
        $name = trim(($doctor->user->prenom ?? '') . ' ' . ($doctor->user->nom ?? ''));

        return $name !== '' ? 'Dr. ' . $name : 'Docteur #' . $doctor->id;
    }

    private function secretaryName(Secretary $secretary): string
    {
        $name = trim(($secretary->user->prenom ?? '') . ' ' . ($secretary->user->nom ?? ''));

        return $name !== '' ? $name : 'Secretaire #' . $secretary->id;
    }

    private function rate(int $value, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 1);
    }
}
