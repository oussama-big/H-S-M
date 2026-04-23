<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Ordonnance;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientDashboardService
{
    public function __construct(private readonly AppointmentWorkflowService $appointmentWorkflowService)
    {
    }

    public function getDashboardData(User $user): array
    {
        $patient = $this->resolvePatient($user);
        $appointments = $patient->appointments
            ->sortBy('appointment_date')
            ->values();
        $consultations = $patient->dossierMedical?->consultations?->sortByDesc('date')->values() ?? collect();
        $ordonnances = $consultations
            ->filter(fn ($consultation) => $consultation->ordonnance !== null)
            ->values();
        $nextAppointment = $appointments
            ->first(fn (Appointment $appointment) => $appointment->status === 'PREVU' && Carbon::parse((string) $appointment->appointment_date)->gte(now()));
        $notifications = Notification::where('user_id', $user->id)
            ->latest('date')
            ->take(6)
            ->get();

        return [
            'patient' => $this->formatPatientProfile($patient),
            'stats' => [
                'consultations' => $consultations->count(),
                'ordonnances' => $ordonnances->count(),
                'upcoming_appointments' => $appointments->filter(fn (Appointment $appointment) => $appointment->status === 'PREVU' && Carbon::parse((string) $appointment->appointment_date)->gte(now()))->count(),
                'active_dossier' => $patient->numDossier ?: 'PAT-' . $patient->id,
            ],
            'next_appointment' => $nextAppointment ? $this->formatNextAppointment($nextAppointment) : null,
            'appointments' => [
                'upcoming' => $appointments
                    ->filter(fn (Appointment $appointment) => $appointment->status === 'PREVU' && Carbon::parse((string) $appointment->appointment_date)->gte(now()))
                    ->values()
                    ->map(fn (Appointment $appointment) => $this->formatAppointment($appointment))
                    ->all(),
                'history' => $appointments
                    ->filter(fn (Appointment $appointment) => $appointment->status !== 'PREVU' || Carbon::parse((string) $appointment->appointment_date)->lt(now()))
                    ->sortByDesc('appointment_date')
                    ->values()
                    ->map(fn (Appointment $appointment) => $this->formatAppointment($appointment))
                    ->all(),
            ],
            'records' => [
                'personal' => $this->formatPatientPersonalInfo($patient),
                'summary' => $this->formatPatientSummary($patient),
            ],
            'consultations' => $consultations
                ->map(fn ($consultation) => $this->formatConsultation($consultation))
                ->all(),
            'ordonnances' => $ordonnances
                ->map(fn ($consultation) => $this->formatOrdonnance($consultation->ordonnance, $consultation))
                ->all(),
            'doctor_options' => $this->doctorOptions()->all(),
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

    public function getAvailabilityCalendar(User $user, int $doctorId, string $month, ?int $ignoreAppointmentId = null): array
    {
        $patient = $this->resolvePatient($user);
        $this->guardAppointmentOwnership($patient, $ignoreAppointmentId);

        return $this->appointmentWorkflowService->getAvailabilityCalendar($doctorId, $month, $ignoreAppointmentId);
    }

    public function getAvailableSlots(User $user, int $doctorId, string $date, ?int $ignoreAppointmentId = null): array
    {
        $patient = $this->resolvePatient($user);
        $this->guardAppointmentOwnership($patient, $ignoreAppointmentId);

        return $this->appointmentWorkflowService->getAvailableSlots($doctorId, $date, $ignoreAppointmentId);
    }

    public function createAppointment(User $user, array $data): array
    {
        $patient = $this->resolvePatient($user);

        $this->appointmentWorkflowService->createAppointment([
            'patient_id' => $patient->id,
            'doctor_id' => (int) $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'reason' => $data['reason'] ?? null,
        ], $user->id);

        return $this->getDashboardData($user);
    }

    public function updateAppointment(User $user, Appointment $appointment, array $data): array
    {
        $patient = $this->resolvePatient($user);
        $appointment = $this->guardOwnedAppointment($patient, $appointment);

        if ($appointment->status !== 'PREVU') {
            throw ValidationException::withMessages([
                'appointment' => 'Seuls les rendez-vous planifies peuvent etre modifies.',
            ]);
        }

        if (Carbon::parse((string) $appointment->appointment_date)->lt(now())) {
            throw ValidationException::withMessages([
                'appointment' => 'Ce rendez-vous est deja passe et ne peut plus etre modifie.',
            ]);
        }

        $this->appointmentWorkflowService->rescheduleAppointment($appointment, [
            'doctor_id' => (int) ($data['doctor_id'] ?? $appointment->doctor_id),
            'appointment_date' => $data['appointment_date'],
            'reason' => $data['reason'] ?? $appointment->reason,
        ], $user->id);

        return $this->getDashboardData($user);
    }

    private function resolvePatient(User $user): Patient
    {
        if ($user->role !== 'PATIENT') {
            throw new AuthorizationException('Acces reserve au dashboard patient.');
        }

        $patient = Patient::with([
            'user',
            'appointments.doctor.user',
            'appointments.doctor.cabinet',
            'dossierMedical.consultations.doctor.user',
            'dossierMedical.consultations.ordonnance',
        ])->find($user->id);

        if (! $patient) {
            throw ValidationException::withMessages([
                'patient' => 'Profil patient introuvable.',
            ]);
        }

        return $patient;
    }

    private function guardOwnedAppointment(Patient $patient, Appointment $appointment): Appointment
    {
        $appointment->loadMissing(['doctor.user', 'doctor.cabinet', 'patient.user']);

        if ((int) $appointment->patient_id !== (int) $patient->id) {
            throw new AuthorizationException('Ce rendez-vous n appartient pas au patient connecte.');
        }

        return $appointment;
    }

    private function guardAppointmentOwnership(Patient $patient, ?int $appointmentId): void
    {
        if (! $appointmentId) {
            return;
        }

        $appointment = Appointment::find($appointmentId);

        if (! $appointment || (int) $appointment->patient_id !== (int) $patient->id) {
            throw new AuthorizationException('Ce rendez-vous n appartient pas au patient connecte.');
        }
    }

    private function formatPatientProfile(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'full_name' => $this->patientName($patient),
            'initials' => $this->initials($patient->user?->prenom, $patient->user?->nom),
            'age_label' => $this->patientAgeLabel($patient),
            'gender' => $patient->gender ?: 'Non renseigne',
            'email' => $patient->user?->email ?: 'Non renseigne',
            'telephone' => $patient->user?->telephone ?: 'Non renseigne',
            'dossier' => $patient->numDossier ?: 'PAT-' . $patient->id,
        ];
    }

    private function formatPatientPersonalInfo(Patient $patient): array
    {
        return [
            'Nom complet' => $this->patientName($patient),
            'Numero de dossier' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'Age' => $this->patientAgeLabel($patient),
            'Genre' => $patient->gender ?: 'Non renseigne',
            'Email' => $patient->user?->email ?: 'Non renseigne',
            'Telephone' => $patient->user?->telephone ?: 'Non renseigne',
            'Groupe sanguin' => $patient->blood_type ?: 'Non renseigne',
            'Contact urgence' => $patient->emergency_contact ?: 'Non renseigne',
        ];
    }

    private function formatPatientSummary(Patient $patient): array
    {
        $dossier = $patient->dossierMedical;
        $latestConsultation = $dossier?->consultations?->sortByDesc('date')->first();
        $snapshot = $this->parseConsultationSnapshot($latestConsultation?->observations);

        return [
            'Dernier diagnostic' => $snapshot['diagnosis'] ?? ($dossier?->diagnosis ?: 'Aucun diagnostic renseigne'),
            'Plan de traitement' => $snapshot['treatment'] ?? ($dossier?->treatment_plan ?: 'Aucun plan de traitement renseigne'),
            'Prochaine visite' => ! empty($snapshot['next_visit'])
                ? Carbon::parse((string) $snapshot['next_visit'])->format('d/m/Y')
                : 'Aucune prochaine visite planifiee',
        ];
    }

    private function formatNextAppointment(Appointment $appointment): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);

        return [
            'appointment_id' => $appointment->id,
            'date' => $date->toDateString(),
            'date_label' => $date->locale('fr')->translatedFormat('l d F Y'),
            'time' => $date->format('H:i'),
            'doctor' => $this->doctorName($appointment->doctor),
            'specialization' => $appointment->doctor?->specialization ?: 'Medecine generale',
            'location' => $appointment->doctor?->cabinet?->nom ?: 'Cabinet principal',
            'reason' => $appointment->reason ?: 'Consultation',
            'turn' => $this->appointmentTurn($appointment),
            'message' => sprintf(
                'Your Turn Number Is %s. Please proceed to your assigned room. Your turn will be called shortly.',
                $this->appointmentTurn($appointment)
            ),
        ];
    }

    private function formatAppointment(Appointment $appointment): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);

        return [
            'id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'doctor' => $this->doctorName($appointment->doctor),
            'doctor_label' => $this->doctorName($appointment->doctor) . ' - ' . ($appointment->doctor?->specialization ?: 'Medecine generale'),
            'specialization' => $appointment->doctor?->specialization ?: 'Medecine generale',
            'location' => $appointment->doctor?->cabinet?->nom ?: 'Cabinet principal',
            'reason' => $appointment->reason ?: 'Consultation',
            'date' => $date->toDateString(),
            'date_label' => $date->locale('fr')->translatedFormat('d M Y'),
            'time' => $date->format('H:i'),
            'datetime' => $date->toIso8601String(),
            'status' => $appointment->status,
            'status_label' => $this->appointmentStatusLabel($appointment),
            'turn' => $this->appointmentTurn($appointment),
            'modifiable' => $appointment->status === 'PREVU' && $date->gte(now()),
        ];
    }

    private function formatConsultation($consultation): array
    {
        $snapshot = $this->parseConsultationSnapshot($consultation->observations);

        return [
            'id' => $consultation->id,
            'date' => Carbon::parse((string) $consultation->date)->locale('fr')->translatedFormat('d M Y'),
            'doctor' => $consultation->doctor ? $this->doctorName($consultation->doctor) : 'Medecin non renseigne',
            'diagnosis' => $snapshot['diagnosis'] ?? ($consultation->dossierMedical?->diagnosis ?: 'Consultation medicale'),
            'treatment' => $snapshot['treatment'] ?? ($consultation->dossierMedical?->treatment_plan ?: 'Aucun plan de traitement renseigne'),
            'notes' => $snapshot['symptoms'] ?? 'Aucune note complementaire.',
            'ordonnance_available' => $consultation->ordonnance !== null,
        ];
    }

    private function formatOrdonnance(Ordonnance $ordonnance, $consultation): array
    {
        $snapshot = $this->parseConsultationSnapshot($consultation->observations);
        $date = Carbon::parse((string) ($ordonnance->date ?: $consultation->date));

        return [
            'id' => $ordonnance->id,
            'date' => $date->format('d/m/Y'),
            'doctor' => $consultation->doctor ? $this->doctorName($consultation->doctor) : 'Medecin non renseigne',
            'specialization' => $consultation->doctor?->specialization ?: 'Medecine generale',
            'diagnosis' => $snapshot['diagnosis'] ?? ($consultation->dossierMedical?->diagnosis ?: 'Consultation'),
            'details' => $ordonnance->details,
            'file_name' => sprintf('ordonnance-%s-%s.html', $date->format('Ymd'), $ordonnance->id),
        ];
    }

    private function doctorOptions(): Collection
    {
        return Doctor::with(['user', 'cabinet'])
            ->get()
            ->filter(fn (Doctor $doctor) => trim(($doctor->user?->prenom ?? '') . ($doctor->user?->nom ?? '')) !== '')
            ->sortBy(fn (Doctor $doctor) => strtolower($this->doctorName($doctor)))
            ->values()
            ->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'label' => $this->doctorName($doctor) . ' - ' . ($doctor->specialization ?: 'Medecine generale'),
                'name' => $this->doctorName($doctor),
                'specialization' => $doctor->specialization ?: 'Medecine generale',
                'cabinet' => $doctor->cabinet?->nom ?: 'Cabinet principal',
            ]);
    }

    private function appointmentStatusLabel(Appointment $appointment): string
    {
        return match ($appointment->status) {
            'PASSE' => 'Complete',
            'ANNULE' => 'Annule',
            default => Carbon::parse((string) $appointment->appointment_date)->lt(now()) ? 'En attente de mise a jour' : 'Planifie',
        };
    }

    private function appointmentTurn(Appointment $appointment): string
    {
        $date = Carbon::parse((string) $appointment->appointment_date);
        $query = Appointment::query()
            ->where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('appointment_date', '<=', $date->toDateTimeString());

        if ($appointment->status === 'PREVU') {
            $query->where('status', 'PREVU');
        } else {
            $query->where('status', '!=', 'ANNULE');
        }

        $position = $query->count();

        return 'T-' . str_pad((string) max(1, $position), 2, '0', STR_PAD_LEFT);
    }

    private function parseConsultationSnapshot(?string $observations): array
    {
        if (! $observations) {
            return [];
        }

        $decoded = json_decode($observations, true);

        if (! is_array($decoded)) {
            return [
                'symptoms' => $observations,
            ];
        }

        return array_filter([
            'diagnosis' => $this->nullIfBlank($decoded['diagnosis'] ?? null),
            'symptoms' => $this->nullIfBlank($decoded['symptoms'] ?? null),
            'treatment' => $this->nullIfBlank($decoded['treatment'] ?? null),
            'next_visit' => $this->nullIfBlank($decoded['next_visit'] ?? null),
            'ordonnance_details' => $this->nullIfBlank($decoded['ordonnance_details'] ?? null),
        ], fn ($value) => $value !== null);
    }

    private function patientName(Patient $patient): string
    {
        $name = trim(($patient->user?->prenom ?? '') . ' ' . ($patient->user?->nom ?? ''));

        return $name !== '' ? $name : 'Patient #' . $patient->id;
    }

    private function doctorName(?Doctor $doctor): string
    {
        $name = trim(($doctor?->user?->prenom ?? '') . ' ' . ($doctor?->user?->nom ?? ''));

        return $name !== '' ? 'Dr. ' . $name : 'Docteur #' . ($doctor?->id ?? '--');
    }

    private function patientAgeLabel(Patient $patient): string
    {
        if (! $patient->date_of_birth) {
            return 'Age non renseigne';
        }

        return Carbon::parse((string) $patient->date_of_birth)->age . ' ans';
    }

    private function initials(?string $firstName, ?string $lastName): string
    {
        $initials = Str::upper(Str::substr(trim((string) $firstName), 0, 1) . Str::substr(trim((string) $lastName), 0, 1));

        return $initials !== '' ? $initials : 'PT';
    }

    private function nullIfBlank(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
