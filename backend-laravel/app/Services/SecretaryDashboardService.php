<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SecretaryDashboardService
{
    public function __construct(
        private readonly AppointmentWorkflowService $appointmentWorkflowService,
        private readonly PatientService $patientService
    ) {
    }

    public function getDashboardData(User $user): array
    {
        $secretary = $this->resolveSecretary($user);
        $todayAppointments = Appointment::with(['patient.user', 'doctor.user', 'doctor.cabinet'])
            ->whereDate('appointment_date', now()->toDateString())
            ->orderBy('appointment_date')
            ->get();
        $doctors = Doctor::with(['user', 'cabinet'])
            ->get()
            ->sortBy(fn (Doctor $doctor) => strtolower($this->doctorName($doctor)))
            ->values();
        $patients = Patient::with('user')
            ->get()
            ->sortBy(fn (Patient $patient) => strtolower($this->patientName($patient)))
            ->values();
        $notifications = Notification::where('user_id', $user->id)
            ->latest('date')
            ->take(6)
            ->get();

        return [
            'secretary' => [
                'id' => $secretary->id,
                'full_name' => $this->secretaryName($secretary),
                'initials' => $this->initials($secretary->user?->prenom, $secretary->user?->nom),
                'assignment' => $secretary->assignment ?: 'Coordination administrative',
                'office_number' => $secretary->office_number ?: 'Accueil principal',
            ],
            'stats' => [
                'appointments_today' => $todayAppointments->count(),
                'waiting_today' => $todayAppointments->where('status', 'PREVU')->count(),
                'completed_today' => $todayAppointments->where('status', 'PASSE')->count(),
                'active_doctors' => $todayAppointments->pluck('doctor_id')->filter()->unique()->count(),
                'patients_total' => $patients->count(),
            ],
            'doctor_panels' => $doctors
                ->map(fn (Doctor $doctor) => $this->formatDoctorPanel($doctor, $todayAppointments->where('doctor_id', $doctor->id)->values()))
                ->values()
                ->all(),
            'patient_options' => $patients->map(fn (Patient $patient) => $this->formatPatientOption($patient))->all(),
            'doctor_options' => $doctors->map(fn (Doctor $doctor) => $this->formatDoctorOption($doctor))->all(),
            'patients' => $patients->map(fn (Patient $patient) => $this->formatPatientRow($patient))->all(),
            'appointments' => Appointment::with(['patient.user', 'doctor.user', 'doctor.cabinet'])
                ->where('appointment_date', '>=', now())
                ->where('status', 'PREVU')
                ->orderBy('appointment_date')
                ->take(20)
                ->get()
                ->map(fn (Appointment $appointment) => $this->formatUpcomingAppointment($appointment))
                ->all(),
            'notifications' => [
                'total' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
                'items' => $notifications->map(fn (Notification $notification) => [
                    'id' => $notification->id,
                    'content' => $notification->contenu,
                    'date' => $notification->date ? Carbon::parse((string) $notification->date)->toDateTimeString() : null,
                    'is_read' => (bool) $notification->is_read,
                ])->all(),
            ],
        ];
    }

    public function createPatient(User $user, array $data): array
    {
        $this->resolveSecretary($user);
        $this->patientService->registerPatient($data);

        return $this->getDashboardData($user);
    }

    public function createAppointment(User $user, array $data): array
    {
        $this->resolveSecretary($user);

        $this->appointmentWorkflowService->createAppointment([
            'patient_id' => (int) $data['patient_id'],
            'doctor_id' => (int) $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'reason' => $data['reason'] ?? null,
        ], $user->id);

        return $this->getDashboardData($user);
    }

    public function getAvailableSlots(User $user, int $doctorId, string $date): array
    {
        $this->resolveSecretary($user);

        return $this->appointmentWorkflowService->getAvailableSlots($doctorId, $date);
    }

    private function resolveSecretary(User $user): Secretary
    {
        if ($user->role !== 'SECRETAIRE') {
            throw new AuthorizationException('Acces reserve au dashboard secretaire.');
        }

        $secretary = Secretary::with('user')->find($user->id);

        if (! $secretary) {
            throw ValidationException::withMessages([
                'secretary' => 'Profil secretaire introuvable.',
            ]);
        }

        return $secretary;
    }

    private function formatDoctorPanel(Doctor $doctor, Collection $appointments): array
    {
        $queue = $appointments
            ->filter(fn (Appointment $appointment) => $appointment->status === 'PREVU')
            ->values();
        $current = $queue->first();
        $next = $queue->skip(1)->first();
        $waitingCount = $queue->count();

        return [
            'doctor_id' => $doctor->id,
            'doctor_name' => $this->doctorName($doctor),
            'specialization' => $doctor->specialization ?: 'Medecine generale',
            'location' => $doctor->cabinet?->nom ?: 'Cabinet principal',
            'has_activity' => $appointments->isNotEmpty(),
            'waiting_count' => $waitingCount,
            'remaining_after_current' => max(0, $waitingCount - ($current ? 1 : 0)),
            'current_consultation' => $current ? $this->formatQueueItem($current, 1) : null,
            'next_consultation' => $next ? $this->formatQueueItem($next, 2) : null,
            'queue' => $queue->map(fn (Appointment $appointment, int $index) => $this->formatQueueItem($appointment, $index + 1))->all(),
            'completed_today' => $appointments->where('status', 'PASSE')->count(),
        ];
    }

    private function formatQueueItem(Appointment $appointment, int $position): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);

        return [
            'appointment_id' => $appointment->id,
            'patient_name' => $this->patientName($appointment->patient),
            'patient_initials' => $this->initials($appointment->patient?->user?->prenom, $appointment->patient?->user?->nom),
            'patient_dossier' => $appointment->patient?->numDossier ?: 'PAT-' . $appointment->patient_id,
            'turn' => 'T-' . str_pad((string) $position, 2, '0', STR_PAD_LEFT),
            'time' => $date->format('H:i'),
            'reason' => $appointment->reason ?: 'Consultation',
            'status_label' => $appointment->status === 'PASSE' ? 'Completee' : 'En attente',
        ];
    }

    private function formatPatientOption(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'label' => $this->patientName($patient),
            'dossier' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'email' => $patient->user?->email ?: 'Non renseigne',
            'telephone' => $patient->user?->telephone ?: 'Non renseigne',
        ];
    }

    private function formatDoctorOption(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'label' => $this->doctorName($doctor) . ' - ' . ($doctor->specialization ?: 'Medecine generale'),
            'name' => $this->doctorName($doctor),
            'specialization' => $doctor->specialization ?: 'Medecine generale',
            'cabinet' => $doctor->cabinet?->nom ?: 'Cabinet principal',
        ];
    }

    private function formatPatientRow(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $this->patientName($patient),
            'dossier' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'email' => $patient->user?->email ?: 'Non renseigne',
            'telephone' => $patient->user?->telephone ?: 'Non renseigne',
            'gender' => $patient->gender ?: 'Non renseigne',
            'age' => $patient->date_of_birth ? Carbon::parse((string) $patient->date_of_birth)->age . ' ans' : 'Age non renseigne',
        ];
    }

    private function formatUpcomingAppointment(Appointment $appointment): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);

        return [
            'id' => $appointment->id,
            'patient' => $this->patientName($appointment->patient),
            'doctor' => $this->doctorName($appointment->doctor),
            'specialization' => $appointment->doctor?->specialization ?: 'Medecine generale',
            'date' => $date->locale('fr')->translatedFormat('d M Y'),
            'time' => $date->format('H:i'),
            'location' => $appointment->doctor?->cabinet?->nom ?: 'Cabinet principal',
            'reason' => $appointment->reason ?: 'Consultation',
        ];
    }

    private function patientName(?Patient $patient): string
    {
        $name = trim(($patient?->user?->prenom ?? '') . ' ' . ($patient?->user?->nom ?? ''));

        return $name !== '' ? $name : 'Patient #' . ($patient?->id ?? '--');
    }

    private function doctorName(Doctor $doctor): string
    {
        $name = trim(($doctor->user?->prenom ?? '') . ' ' . ($doctor->user?->nom ?? ''));

        return $name !== '' ? 'Dr. ' . $name : 'Docteur #' . $doctor->id;
    }

    private function secretaryName(Secretary $secretary): string
    {
        $name = trim(($secretary->user?->prenom ?? '') . ' ' . ($secretary->user?->nom ?? ''));

        return $name !== '' ? $name : 'Secretaire #' . $secretary->id;
    }

    private function initials(?string $firstName, ?string $lastName): string
    {
        $initials = Str::upper(Str::substr(trim((string) $firstName), 0, 1) . Str::substr(trim((string) $lastName), 0, 1));

        return $initials !== '' ? $initials : 'SC';
    }
}
