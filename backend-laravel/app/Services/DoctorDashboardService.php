<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\DossierMedical;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Secretary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DoctorDashboardService
{
    public function __construct(
        private readonly AppointmentWorkflowService $appointmentWorkflowService,
        private readonly NotificationService $notificationService
    ) {
    }

    public function getDashboardData(User $user): array
    {
        $doctor = $this->resolveDoctor($user);

        $appointments = Appointment::with([
            'patient.user',
            'patient.dossierMedical.consultations.doctor.user',
            'consultation.ordonnance',
        ])
            ->where('doctor_id', $doctor->id)
            ->orderBy('appointment_date')
            ->get();

        $patientIds = $appointments
            ->pluck('patient_id')
            ->filter()
            ->unique()
            ->values();

        $patients = $patientIds->isEmpty()
            ? collect()
            : Patient::with([
                'user',
                'appointments' => function ($query) use ($doctor) {
                    $query->where('doctor_id', $doctor->id)
                        ->with(['consultation.ordonnance'])
                        ->orderByDesc('appointment_date');
                },
                'dossierMedical.consultations' => function ($query) {
                    $query->with(['doctor.user', 'ordonnance'])
                        ->orderByDesc('date');
                },
            ])
                ->whereIn('id', $patientIds)
                ->get()
                ->sortBy(fn (Patient $patient) => strtolower($this->patientName($patient)))
                ->values();

        $availablePatients = Patient::with('user')
            ->get()
            ->sortBy(fn (Patient $patient) => strtolower($this->patientName($patient)))
            ->values();

        $todayAppointments = $appointments
            ->filter(fn (Appointment $appointment) => Carbon::parse((string) $appointment->appointment_date)->isToday())
            ->values();

        $queueAppointments = $todayAppointments
            ->filter(fn (Appointment $appointment) => $appointment->status === 'PREVU')
            ->values();

        $currentAppointment = $queueAppointments->first();
        $todayCompleted = $todayAppointments->where('status', 'PASSE')->count();
        $todayWaiting = $queueAppointments->count();
        $todayCancelled = $todayAppointments->where('status', 'ANNULE')->count();
        $delayMinutes = $todayAppointments
            ->map(fn (Appointment $appointment) => $this->consultationDelayMinutes($appointment))
            ->filter(fn ($value) => $value !== null)
            ->values();

        $todayAppointmentsFormatted = $todayAppointments
            ->sortBy('appointment_date')
            ->values()
            ->map(fn (Appointment $appointment) => $this->formatScheduleAppointment($appointment))
            ->all();

        $upcomingAppointmentsFormatted = $appointments
            ->filter(function (Appointment $appointment) {
                $date = Carbon::parse((string) $appointment->appointment_date);

                return $date->isFuture() && $date->lte(now()->copy()->addDays(7)->endOfDay());
            })
            ->sortBy('appointment_date')
            ->values()
            ->map(fn (Appointment $appointment) => $this->formatScheduleAppointment($appointment))
            ->all();

        $contacts = $this->buildContacts($doctor);
        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(6)
            ->get();

        return [
            'doctor' => $this->formatDoctor($doctor),
            'stats' => [
                'tracked_patients' => $patients->count(),
                'appointments_today' => $todayAppointments->count(),
                'appointments_this_week' => $appointments
                    ->filter(fn (Appointment $appointment) => Carbon::parse((string) $appointment->appointment_date)->betweenIncluded(now()->startOfDay(), now()->copy()->addDays(7)->endOfDay()))
                    ->count(),
                'consultations_total' => Consultation::where('doctor_id', $doctor->id)->count(),
                'completed_today' => $todayCompleted,
                'waiting_today' => $todayWaiting,
                'cancelled_today' => $todayCancelled,
                'avg_consultation_delay' => $delayMinutes->isEmpty() ? null : (int) round($delayMinutes->avg()),
                'completion_rate' => $this->rate($todayCompleted, $todayAppointments->count()),
            ],
            'current_consultation' => $currentAppointment
                ? $this->formatQueueAppointment($currentAppointment, $doctor, 1)
                : null,
            'queue' => $queueAppointments
                ->values()
                ->map(fn (Appointment $appointment, int $index) => $this->formatQueueAppointment($appointment, $doctor, $index + 1))
                ->all(),
            'schedule' => [
                'today' => $todayAppointmentsFormatted,
                'upcoming' => $upcomingAppointmentsFormatted,
                'all' => $appointments
                    ->map(fn (Appointment $appointment) => $this->formatScheduleAppointment($appointment))
                    ->values()
                    ->all(),
            ],
            'patients' => $patients
                ->map(fn (Patient $patient) => $this->formatPatient($patient))
                ->values()
                ->all(),
            'consultations' => $appointments
                ->sortByDesc('appointment_date')
                ->values()
                ->map(fn (Appointment $appointment) => $this->formatConsultationRow($appointment, $doctor))
                ->all(),
            'records' => $patients
                ->map(fn (Patient $patient) => $this->formatRecord($patient))
                ->values()
                ->all(),
            'contacts' => $contacts->values()->all(),
            'patient_options' => $availablePatients
                ->map(fn (Patient $patient) => $this->formatPatientOption($patient))
                ->values()
                ->all(),
            'notifications' => [
                'total' => Notification::where('user_id', $user->id)->count(),
                'unread' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
                'items' => $notifications->map(function (Notification $notification) {
                    return [
                        'id' => $notification->id,
                        'content' => $notification->contenu,
                        'date' => $notification->date
                            ? Carbon::parse((string) $notification->date)->toDateTimeString()
                            : null,
                        'is_read' => (bool) $notification->is_read,
                    ];
                })->values()->all(),
            ],
        ];
    }

    public function createAppointment(User $user, array $data): array
    {
        $doctor = $this->resolveDoctor($user);
        $this->appointmentWorkflowService->createAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => (int) $data['patient_id'],
            'appointment_date' => $data['appointment_date'],
            'reason' => $data['reason'] ?? null,
        ], $user->id);

        return $this->getDashboardData($user);
    }

    public function findPatientByEmail(User $user, string $email): ?array
    {
        $this->resolveDoctor($user);

        $patient = Patient::with('user')
            ->whereHas('user', function ($query) use ($email) {
                $query->whereRaw('LOWER(email) = ?', [Str::lower($email)]);
            })
            ->first();

        return $patient ? $this->formatPatientOption($patient) : null;
    }

    public function saveConsultationNotes(User $user, Appointment $appointment, array $data): array
    {
        $doctor = $this->resolveDoctor($user);
        $appointment = $this->guardAppointmentOwnership($doctor, $appointment);
        $preparedData = $this->normalizeConsultationData($appointment, $data);

        if ($appointment->status === 'ANNULE') {
            throw ValidationException::withMessages([
                'appointment' => 'Impossible de modifier un rendez-vous annule.',
            ]);
        }

        DB::transaction(function () use ($doctor, $appointment, $preparedData) {
            $this->persistConsultationSnapshot($doctor, $appointment, $preparedData);
        });

        return $this->getDashboardData($user);
    }

    public function completeConsultation(User $user, Appointment $appointment, array $data): array
    {
        $doctor = $this->resolveDoctor($user);
        $appointment = $this->guardAppointmentOwnership($doctor, $appointment);
        $preparedData = $this->normalizeConsultationData($appointment, $data);
        $nextVisitDate = ! empty($preparedData['next_visit'])
            ? Carbon::parse((string) $preparedData['next_visit'])
            : null;

        if ($appointment->status === 'ANNULE') {
            throw ValidationException::withMessages([
                'appointment' => 'Impossible de cloturer un rendez-vous annule.',
            ]);
        }

        DB::transaction(function () use ($doctor, $appointment, $preparedData, $nextVisitDate) {
            $this->persistConsultationSnapshot($doctor, $appointment, $preparedData);

            $appointment->update([
                'status' => 'PASSE',
            ]);

            $this->createFollowUpAppointment($doctor, $appointment, $nextVisitDate);
        });

        $this->notifyPatientIfOrdonnanceReady($appointment, $preparedData);
        $this->notifySecretariesForNextPatient($doctor);

        return $this->getDashboardData($user);
    }

    public function rescheduleAppointment(User $user, Appointment $appointment, array $data): array
    {
        $doctor = $this->resolveDoctor($user);
        $appointment = $this->guardAppointmentOwnership($doctor, $appointment);

        if ($appointment->status === 'PASSE') {
            throw ValidationException::withMessages([
                'appointment' => 'Ce rendez-vous est deja cloture. Creez plutot un suivi.',
            ]);
        }

        $this->appointmentWorkflowService->rescheduleAppointment($appointment, [
            'doctor_id' => $doctor->id,
            'appointment_date' => $data['appointment_date'],
            'reason' => $data['reason'] ?? null,
        ], $user->id);

        return $this->getDashboardData($user);
    }

    private function resolveDoctor(User $user): Doctor
    {
        if ($user->role !== 'MEDECIN') {
            throw new AuthorizationException('Acces reserve au dashboard medecin.');
        }

        $doctor = Doctor::with(['user', 'cabinet'])->find($user->id);

        if (! $doctor) {
            throw ValidationException::withMessages([
                'doctor' => 'Profil medecin introuvable.',
            ]);
        }

        return $doctor;
    }

    private function guardAppointmentOwnership(Doctor $doctor, Appointment $appointment): Appointment
    {
        $appointment->loadMissing([
            'patient.user',
            'patient.dossierMedical',
            'consultation.ordonnance',
        ]);

        if ((int) $appointment->doctor_id !== (int) $doctor->id) {
            throw new AuthorizationException('Ce rendez-vous n appartient pas au medecin connecte.');
        }

        return $appointment;
    }

    private function persistConsultationSnapshot(Doctor $doctor, Appointment $appointment, array $data): Consultation
    {
        $dossierMedical = DossierMedical::firstOrCreate(
            ['patient_id' => $appointment->patient_id],
            [
                'diagnosis' => null,
                'treatment_plan' => null,
            ]
        );

        $dossierMedical->update([
            'diagnosis' => array_key_exists('diagnosis', $data)
                ? $this->nullIfBlank($data['diagnosis'])
                : $dossierMedical->diagnosis,
            'treatment_plan' => array_key_exists('treatment', $data)
                ? $this->nullIfBlank($data['treatment'])
                : $dossierMedical->treatment_plan,
        ]);

        $snapshot = [
            'diagnosis' => $this->nullIfBlank($data['diagnosis'] ?? $dossierMedical->diagnosis),
            'symptoms' => $this->nullIfBlank($data['symptoms'] ?? null),
            'treatment' => $this->nullIfBlank($data['treatment'] ?? $dossierMedical->treatment_plan),
            'next_visit' => $this->nullIfBlank($data['next_visit'] ?? null),
            'ordonnance_details' => $this->nullIfBlank($data['ordonnance_details'] ?? null),
            'patient' => [
                'name' => $this->patientName($appointment->patient),
                'dossier' => $appointment->patient?->numDossier ?: 'PAT-' . $appointment->patient_id,
                'email' => $appointment->patient?->user?->email,
                'telephone' => $appointment->patient?->user?->telephone,
            ],
        ];

        $consultation = Consultation::firstOrNew([
            'appointment_id' => $appointment->id,
        ]);

        $consultation->doctor_id = $doctor->id;
        $consultation->dossier_medical_id = $dossierMedical->id;
        $consultation->date = Carbon::parse((string) $appointment->appointment_date)->toDateString();
        $consultation->observations = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $consultation->save();

        if (array_key_exists('ordonnance_details', $data)) {
            $this->syncOrdonnance($consultation, $appointment, $data['ordonnance_details'] ?? null);
        }

        return $consultation;
    }

    private function normalizeConsultationData(Appointment $appointment, array $data): array
    {
        if (! array_key_exists('next_visit', $data)) {
            return $data;
        }

        $nextVisitDate = $this->normalizeNextVisitDate($appointment, $data['next_visit'] ?? null);
        $data['next_visit'] = $nextVisitDate?->toDateString();

        return $data;
    }

    private function normalizeNextVisitDate(Appointment $appointment, mixed $nextVisit): ?Carbon
    {
        $nextVisitValue = $this->nullIfBlank(is_string($nextVisit) ? $nextVisit : null);

        if (! $nextVisitValue) {
            return null;
        }

        $baseDate = Carbon::parse((string) $appointment->appointment_date)->startOfDay();
        $followUpDate = Carbon::parse($nextVisitValue)->startOfDay();

        if ($followUpDate->isSameDay($baseDate)) {
            throw ValidationException::withMessages([
                'next_visit' => 'La prochaine visite doit etre programmee un autre jour.',
            ]);
        }

        if ($followUpDate->lte($baseDate)) {
            throw ValidationException::withMessages([
                'next_visit' => 'La prochaine visite doit etre posterieure a la consultation actuelle.',
            ]);
        }

        return $followUpDate;
    }

    private function assertAppointmentAvailability(Doctor $doctor, int $patientId, Carbon $appointmentDate, ?int $ignoreAppointmentId = null): void
    {
        $hourStart = $appointmentDate->copy()->startOfHour();
        $hourEnd = $appointmentDate->copy()->endOfHour();

        $doctorConflict = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', '!=', 'ANNULE')
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->whereBetween('appointment_date', [
                $hourStart->toDateTimeString(),
                $hourEnd->toDateTimeString(),
            ])
            ->exists();

        if ($doctorConflict) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Ce medecin a deja un rendez-vous sur ce creneau horaire.',
            ]);
        }

        $patientConflict = Appointment::query()
            ->where('patient_id', $patientId)
            ->where('status', '!=', 'ANNULE')
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->whereDate('appointment_date', $appointmentDate->toDateString())
            ->exists();

        if ($patientConflict) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Ce patient a deja un rendez-vous pour cette journee.',
            ]);
        }
    }

    private function createFollowUpAppointment(Doctor $doctor, Appointment $appointment, ?Carbon $nextVisitDate): ?Appointment
    {
        if (! $nextVisitDate) {
            return null;
        }

        $baseDate = Carbon::parse((string) $appointment->appointment_date);
        $followUpDate = $nextVisitDate->copy()->setTime(
            (int) $baseDate->format('H'),
            (int) $baseDate->format('i')
        );

        if (! $followUpDate->isFuture()) {
            throw ValidationException::withMessages([
                'next_visit' => 'La prochaine visite doit etre programmee dans le futur.',
            ]);
        }

        return $this->appointmentWorkflowService->createAppointment([
            'doctor_id' => $doctor->id,
            'patient_id' => (int) $appointment->patient_id,
            'appointment_date' => $followUpDate->format('Y-m-d\TH:i'),
            'reason' => $appointment->reason
                ? 'Suivi - ' . $appointment->reason
                : 'Consultation de suivi',
        ], $doctor->id);
    }

    private function formatDoctor(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'full_name' => $this->doctorName($doctor),
            'initials' => $this->initials($doctor->user?->prenom, $doctor->user?->nom),
            'specialization' => $doctor->specialization ?: 'Medecine generale',
            'cabinet' => $doctor->cabinet?->nom ?: 'Cabinet principal',
            'telephone' => $doctor->user?->telephone,
            'email' => $doctor->user?->email,
        ];
    }

    private function formatQueueAppointment(Appointment $appointment, Doctor $doctor, int $position): array
    {
        $patient = $appointment->patient;
        $consultation = $appointment->consultation;
        $dossierMedical = $patient?->dossierMedical;
        $snapshot = $this->parseConsultationSnapshot($consultation?->observations);

        return [
            'appointment_id' => $appointment->id,
            'consultation_id' => $consultation?->id,
            'patient_id' => $patient?->id,
            'patient_name' => $this->patientName($patient),
            'patient_initials' => $this->initials($patient?->user?->prenom, $patient?->user?->nom),
            'patient_display_id' => $patient?->numDossier ?: 'PAT-' . $appointment->patient_id,
            'patient_contact' => $patient?->user?->telephone ?: ($patient?->user?->email ?: 'Contact non renseigne'),
            'patient_gender' => $patient?->gender ?: 'Non renseigne',
            'turn' => $this->appointmentTurn($position, $appointment),
            'age_label' => $this->patientAgeLabel($patient),
            'condition' => $appointment->reason ?: ($dossierMedical?->diagnosis ?: 'Consultation generale'),
            'specialty' => $doctor->specialization ?: 'Medecine generale',
            'arrival' => Carbon::parse((string) $appointment->appointment_date)->format('H:i'),
            'status' => $appointment->status,
            'status_label' => $this->appointmentStatusLabel($appointment, $consultation !== null),
            'room' => $doctor->cabinet?->nom ?: 'Cabinet principal',
            'position' => $position,
            'diagnosis' => $snapshot['diagnosis'] ?? ($dossierMedical?->diagnosis ?: ''),
            'symptoms' => $snapshot['symptoms'] ?? '',
            'treatment' => $snapshot['treatment'] ?? ($dossierMedical?->treatment_plan ?: ''),
            'next_visit' => $snapshot['next_visit'] ?? '',
            'ordonnance_details' => $snapshot['ordonnance_details'] ?? ($consultation?->ordonnance?->details ?: ''),
        ];
    }

    private function formatPatientOption(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'label' => $this->patientName($patient),
            'dossier' => $patient->numDossier,
            'email' => $patient->user?->email,
            'telephone' => $patient->user?->telephone,
        ];
    }

    private function formatScheduleAppointment(Appointment $appointment): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);

        return [
            'id' => $appointment->id,
            'patient' => $this->patientName($appointment->patient),
            'patient_id' => $appointment->patient_id,
            'type' => $appointment->reason ?: 'Consultation',
            'status' => $appointment->status,
            'status_label' => $this->appointmentStatusLabel($appointment, $appointment->consultation !== null),
            'date' => $date->toDateString(),
            'date_label' => $date->locale('fr')->translatedFormat('d M Y'),
            'time' => $date->format('H:i'),
            'datetime' => $date->toIso8601String(),
        ];
    }

    private function formatPatient(Patient $patient): array
    {
        $appointments = $patient->appointments
            ->sortByDesc('appointment_date')
            ->values();

        $latestAppointment = $appointments->first();
        $dossierMedical = $patient->dossierMedical;
        $consultations = $dossierMedical?->consultations ?? collect();
        $latestConsultation = $consultations->sortByDesc('date')->first();
        $snapshot = $this->parseConsultationSnapshot($latestConsultation?->observations);

        return [
            'id' => $patient->id,
            'name' => $this->patientName($patient),
            'initials' => $this->initials($patient->user?->prenom, $patient->user?->nom),
            'display_id' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'condition' => $latestAppointment?->reason
                ?: ($snapshot['diagnosis'] ?? ($dossierMedical?->diagnosis ?: 'Suivi general')),
            'age_label' => $this->patientAgeLabel($patient),
            'gender' => $patient->gender ?: '--',
            'phone' => $patient->user?->telephone ?: 'Non renseigne',
            'email' => $patient->user?->email ?: 'Non renseigne',
            'status_label' => $latestAppointment ? $this->appointmentStatusLabel($latestAppointment, $latestConsultation !== null) : 'Nouveau',
            'history' => $consultations
                ->sortByDesc('date')
                ->values()
                ->map(function (Consultation $consultation) {
                    $snapshot = $this->parseConsultationSnapshot($consultation->observations);

                    return [
                        'date' => Carbon::parse((string) $consultation->date)->locale('fr')->translatedFormat('d M Y'),
                        'diagnosis' => $snapshot['diagnosis']
                            ?? ($consultation->dossierMedical?->diagnosis ?: 'Consultation medicale'),
                        'doctor' => $consultation->doctor ? $this->doctorName($consultation->doctor) : 'Medecin non renseigne',
                        'notes' => $snapshot['symptoms']
                            ?? ($snapshot['treatment'] ?? 'Aucune note complementaire.'),
                    ];
                })
                ->all(),
        ];
    }

    private function formatConsultationRow(Appointment $appointment, Doctor $doctor): array
    {
        $date = Carbon::parse((string) $appointment->appointment_date);
        $consultation = $appointment->consultation;
        $snapshot = $this->parseConsultationSnapshot($consultation?->observations);

        return [
            'appointment_id' => $appointment->id,
            'consultation_id' => $consultation?->id,
            'patient' => $this->patientName($appointment->patient),
            'time' => $date->format('H:i'),
            'date_label' => $date->locale('fr')->translatedFormat('d M Y'),
            'room' => $doctor->cabinet?->nom ?: 'Cabinet principal',
            'status' => $this->appointmentStatusLabel($appointment, $consultation !== null),
            'notes' => $snapshot['treatment']
                ?? ($snapshot['symptoms'] ?? ($appointment->reason ?: 'Aucune note medicale.')),
        ];
    }

    private function formatRecord(Patient $patient): array
    {
        $dossierMedical = $patient->dossierMedical;
        $latestConsultation = $dossierMedical?->consultations?->sortByDesc('date')->first();
        $snapshot = $this->parseConsultationSnapshot($latestConsultation?->observations);
        $updatedAt = collect([
            $dossierMedical?->updated_at,
            $latestConsultation?->updated_at,
        ])
            ->filter()
            ->map(fn ($date) => Carbon::parse((string) $date))
            ->sortByDesc(fn (Carbon $date) => $date->timestamp)
            ->first();

        return [
            'title' => 'Dossier ' . ($patient->numDossier ?: 'PAT-' . $patient->id),
            'dossier_number' => $patient->numDossier ?: 'PAT-' . $patient->id,
            'patient' => $this->patientName($patient),
            'patient_email' => $patient->user?->email ?: 'Non renseigne',
            'summary' => $snapshot['diagnosis']
                ?? ($dossierMedical?->diagnosis ?: 'Aucun diagnostic renseigne.'),
            'diagnosis' => $snapshot['diagnosis']
                ?? ($dossierMedical?->diagnosis ?: 'Aucun diagnostic renseigne.'),
            'treatment' => $snapshot['treatment']
                ?? ($dossierMedical?->treatment_plan ?: 'Aucun plan de traitement renseigne.'),
            'next_visit' => ! empty($snapshot['next_visit'])
                ? Carbon::parse((string) $snapshot['next_visit'])->format('d/m/Y')
                : 'Aucune prochaine visite planifiee',
            'updated_at' => $updatedAt ? $updatedAt->diffForHumans() : 'Jamais mis a jour',
        ];
    }

    private function buildContacts(Doctor $doctor): Collection
    {
        $contacts = collect();

        if ($doctor->cabinet) {
            $contacts->push([
                'name' => $doctor->cabinet->nom,
                'role' => 'Cabinet',
                'details' => $doctor->cabinet->telephone ?: ($doctor->cabinet->email ?: 'Contact non renseigne'),
                'extra' => $doctor->cabinet->adresse ?: 'Cabinet affecte au medecin connecte.',
            ]);
        }

        Secretary::with('user')
            ->get()
            ->each(function (Secretary $secretary) use ($contacts) {
                $contacts->push([
                    'name' => trim(($secretary->user?->prenom ?? '') . ' ' . ($secretary->user?->nom ?? '')),
                    'role' => 'Secretaire',
                    'details' => $secretary->user?->telephone ?: ($secretary->user?->email ?: 'Contact non renseigne'),
                    'extra' => $secretary->assignment ?: 'Coordination administrative',
                ]);
            });

        User::query()
            ->where('role', 'ADMIN')
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get()
            ->each(function (User $admin) use ($contacts) {
                $contacts->push([
                    'name' => trim(($admin->prenom ?? '') . ' ' . ($admin->nom ?? '')),
                    'role' => 'Administration',
                    'details' => $admin->telephone ?: ($admin->email ?: 'Contact non renseigne'),
                    'extra' => 'Support backoffice et supervision',
                ]);
            });

        return $contacts
            ->filter(fn (array $contact) => trim((string) $contact['name']) !== '')
            ->unique(fn (array $contact) => $contact['role'] . '|' . $contact['name'])
            ->values();
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

    private function syncOrdonnance(Consultation $consultation, Appointment $appointment, mixed $details): void
    {
        $cleanDetails = $this->nullIfBlank(is_string($details) ? $details : null);
        $consultation->loadMissing('ordonnance');

        if (! $cleanDetails) {
            $consultation->ordonnance?->delete();
            return;
        }

        $consultation->ordonnance()->updateOrCreate(
            ['consultation_id' => $consultation->id],
            [
                'details' => $cleanDetails,
                'date' => Carbon::parse((string) $appointment->appointment_date)->toDateString(),
            ]
        );
    }

    private function consultationDelayMinutes(Appointment $appointment): ?int
    {
        if (! $appointment->consultation || ! $appointment->consultation->created_at) {
            return null;
        }

        $appointmentDate = Carbon::parse((string) $appointment->appointment_date);
        $consultationCreatedAt = Carbon::parse((string) $appointment->consultation->created_at);

        return abs($consultationCreatedAt->diffInMinutes($appointmentDate));
    }

    private function appointmentStatusLabel(Appointment $appointment, bool $hasConsultation): string
    {
        return match ($appointment->status) {
            'PASSE' => 'Completee',
            'ANNULE' => 'Annulee',
            default => $hasConsultation ? 'En cours' : 'Planifiee',
        };
    }

    private function patientName(?Patient $patient): string
    {
        if (! $patient) {
            return 'Patient inconnu';
        }

        $name = trim(($patient->user?->prenom ?? '') . ' ' . ($patient->user?->nom ?? ''));

        return $name !== '' ? $name : 'Patient #' . $patient->id;
    }

    private function doctorName(Doctor $doctor): string
    {
        $name = trim(($doctor->user?->prenom ?? '') . ' ' . ($doctor->user?->nom ?? ''));

        return $name !== '' ? 'Dr. ' . $name : 'Docteur #' . $doctor->id;
    }

    private function patientAgeLabel(?Patient $patient): string
    {
        if (! $patient?->date_of_birth) {
            return 'Age non renseigne';
        }

        $years = Carbon::parse((string) $patient->date_of_birth)->age;

        return $years . ' ans';
    }

    private function appointmentTurn(int $position, Appointment $appointment): string
    {
        if ($position > 0) {
            return 'T-' . str_pad((string) $position, 2, '0', STR_PAD_LEFT);
        }

        return 'A-' . str_pad((string) $appointment->id, 3, '0', STR_PAD_LEFT);
    }

    private function initials(?string $firstName, ?string $lastName): string
    {
        $initials = Str::upper(
            Str::substr(trim((string) $firstName), 0, 1)
            . Str::substr(trim((string) $lastName), 0, 1)
        );

        return $initials !== '' ? $initials : 'NA';
    }

    private function nullIfBlank(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function rate(int $value, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 1);
    }

    private function notifyPatientIfOrdonnanceReady(Appointment $appointment, array $data): void
    {
        $ordonnance = $this->nullIfBlank($data['ordonnance_details'] ?? null);

        if (! $ordonnance || ! $appointment->patient_id) {
            return;
        }

        $this->notificationService->createNotification([
            'user_id' => (int) $appointment->patient_id,
            'contenu' => sprintf(
                'Votre ordonnance est disponible suite a la consultation du %s.',
                Carbon::parse((string) $appointment->appointment_date)->format('d/m/Y')
            ),
        ]);
    }

    private function notifySecretariesForNextPatient(Doctor $doctor): void
    {
        $nextAppointment = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->id)
            ->where('status', 'PREVU')
            ->whereDate('appointment_date', now()->toDateString())
            ->orderBy('appointment_date')
            ->first();

        $message = $nextAppointment
            ? sprintf(
                'La consultation de %s est terminee. Merci d appeler %s (%s) pour %s a %s.',
                $this->doctorName($doctor),
                $this->patientName($nextAppointment->patient),
                $this->appointmentTurn(1, $nextAppointment),
                $doctor->cabinet?->nom ?: 'le cabinet',
                Carbon::parse((string) $nextAppointment->appointment_date)->format('H:i')
            )
            : sprintf(
                'La consultation de %s est terminee. Aucun autre patient n est en attente pour aujourd hui.',
                $this->doctorName($doctor)
            );

        Secretary::query()
            ->pluck('id')
            ->each(function (int $secretaryId) use ($message) {
                $this->notificationService->createNotification([
                    'user_id' => $secretaryId,
                    'contenu' => $message,
                ]);
            });
    }
}
