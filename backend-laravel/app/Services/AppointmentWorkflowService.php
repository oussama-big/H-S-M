<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentWorkflowService
{
    private const SLOT_HOURS = [8, 9, 10, 11, 12, 14, 15];

    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function createAppointment(array $data, ?int $actorUserId = null): Appointment
    {
        $doctor = $this->findDoctor((int) $data['doctor_id']);
        $patient = $this->findPatient((int) $data['patient_id']);
        $appointmentDate = $this->resolveAppointmentDate($data['appointment_date'] ?? null);

        $this->assertAvailability($doctor->id, $patient->id, $appointmentDate);

        $appointment = DB::transaction(function () use ($doctor, $patient, $appointmentDate, $data) {
            return Appointment::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => $appointmentDate,
                'status' => 'PREVU',
                'reason' => $this->nullIfBlank($data['reason'] ?? null),
            ]);
        });

        $appointment->loadMissing(['doctor.user', 'doctor.cabinet', 'patient.user']);
        $this->notifyDoctorForAppointment($appointment, $actorUserId);

        return $appointment;
    }

    public function rescheduleAppointment(Appointment $appointment, array $data, ?int $actorUserId = null): Appointment
    {
        $appointment->loadMissing(['doctor.user', 'doctor.cabinet', 'patient.user', 'consultation']);

        $doctorId = (int) ($data['doctor_id'] ?? $appointment->doctor_id);
        $patientId = (int) $appointment->patient_id;
        $doctor = $this->findDoctor($doctorId);
        $patient = $this->findPatient($patientId);
        $appointmentDate = $this->resolveAppointmentDate($data['appointment_date'] ?? null);

        if ($appointment->status === 'PASSE') {
            throw ValidationException::withMessages([
                'appointment' => 'Ce rendez-vous est deja cloture et ne peut plus etre reprogramme.',
            ]);
        }

        $this->assertAvailability($doctor->id, $patient->id, $appointmentDate, (int) $appointment->id);

        DB::transaction(function () use ($appointment, $doctor, $appointmentDate, $data) {
            $appointment->update([
                'doctor_id' => $doctor->id,
                'appointment_date' => $appointmentDate,
                'status' => 'PREVU',
                'reason' => array_key_exists('reason', $data)
                    ? $this->nullIfBlank($data['reason'])
                    : $appointment->reason,
            ]);

            if ($appointment->consultation) {
                $appointment->consultation->update([
                    'date' => $appointmentDate->toDateString(),
                ]);
            }
        });

        $appointment->refresh()->loadMissing(['doctor.user', 'doctor.cabinet', 'patient.user']);

        if ($actorUserId !== $doctor->id) {
            $this->notificationService->createNotification([
                'user_id' => $doctor->id,
                'contenu' => sprintf(
                    'Le rendez-vous de %s a ete reprogramme au %s a %s.',
                    $this->patientName($appointment->patient),
                    $appointmentDate->format('d/m/Y'),
                    $appointmentDate->format('H:i')
                ),
            ]);
        }

        return $appointment;
    }

    public function getAvailableSlots(int $doctorId, Carbon|string $date, ?int $ignoreAppointmentId = null): array
    {
        $this->findDoctor($doctorId);

        $day = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse((string) $date)->startOfDay();

        if (! $this->isBusinessDay($day)) {
            return [];
        }

        $bookedSlots = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'ANNULE')
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->whereDate('appointment_date', $day->toDateString())
            ->get()
            ->map(fn (Appointment $appointment) => Carbon::parse((string) $appointment->appointment_date)->format('H:00'))
            ->unique()
            ->values()
            ->all();

        $slots = [];

        foreach (self::SLOT_HOURS as $hour) {
            $slot = $day->copy()->setTime($hour, 0);
            $slotKey = $slot->format('H:00');
            $isPast = $slot->lte(now());

            if ($isPast || in_array($slotKey, $bookedSlots, true)) {
                continue;
            }

            $slots[] = [
                'value' => $slot->format('Y-m-d\TH:i'),
                'label' => $slot->format('H:i'),
                'date' => $slot->toDateString(),
                'hour' => $slotKey,
            ];
        }

        return $slots;
    }

    public function getAvailabilityCalendar(int $doctorId, Carbon|string $month, ?int $ignoreAppointmentId = null): array
    {
        $this->findDoctor($doctorId);

        $cursor = $month instanceof Carbon
            ? $month->copy()->startOfMonth()
            : Carbon::parse((string) $month . '-01')->startOfMonth();

        $end = $cursor->copy()->endOfMonth();
        $calendar = [];

        while ($cursor->lte($end)) {
            $calendar[$cursor->toDateString()] = [
                'count' => count($this->getAvailableSlots($doctorId, $cursor, $ignoreAppointmentId)),
                'is_selectable' => $this->isBusinessDay($cursor) && $cursor->endOfDay()->gt(now()),
            ];

            $cursor->addDay();
        }

        return $calendar;
    }

    public function assertAvailability(int $doctorId, int $patientId, Carbon $appointmentDate, ?int $ignoreAppointmentId = null): void
    {
        $this->validateSlotWindow($appointmentDate);

        $hourStart = $appointmentDate->copy()->startOfHour();
        $hourEnd = $appointmentDate->copy()->endOfHour();

        $doctorConflict = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'ANNULE')
            ->when($ignoreAppointmentId, fn ($query) => $query->where('id', '!=', $ignoreAppointmentId))
            ->whereBetween('appointment_date', [
                $hourStart->toDateTimeString(),
                $hourEnd->toDateTimeString(),
            ])
            ->exists();

        if ($doctorConflict) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Ce medecin a deja un rendez-vous sur ce creneau horaire. Choisissez une autre heure.',
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
                'appointment_date' => 'Ce patient a deja un rendez-vous pour cette journee. Une seule reservation par jour est autorisee.',
            ]);
        }
    }

    private function validateSlotWindow(Carbon $appointmentDate): void
    {
        if ($appointmentDate->lte(now())) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Le rendez-vous doit etre programme dans le futur.',
            ]);
        }

        if ($appointmentDate->minute !== 0 || $appointmentDate->second !== 0) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Le rendez-vous doit etre positionne sur une heure pleine disponible.',
            ]);
        }

        if (! $this->isBusinessDay($appointmentDate)) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Les rendez-vous ne sont pas disponibles le samedi ni le dimanche.',
            ]);
        }

        $hour = (int) $appointmentDate->format('H');

        if ($hour === 13) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Le creneau 13:00-14:00 est reserve a la pause dejeuner.',
            ]);
        }

        if (! in_array($hour, self::SLOT_HOURS, true)) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Les rendez-vous sont disponibles de 08:00 a 12:00 puis de 14:00 a 16:00, avec une derniere prise a 15:00.',
            ]);
        }
    }

    private function isBusinessDay(Carbon $date): bool
    {
        return ! $date->isSaturday() && ! $date->isSunday();
    }

    private function resolveAppointmentDate(mixed $value): Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            throw ValidationException::withMessages([
                'appointment_date' => 'Veuillez choisir une date et une heure pour le rendez-vous.',
            ]);
        }

        $date = Carbon::parse($value);

        return $date->copy()->setTime((int) $date->format('H'), (int) $date->format('i'), 0);
    }

    private function notifyDoctorForAppointment(Appointment $appointment, ?int $actorUserId = null): void
    {
        if (! $appointment->doctor || (int) $appointment->doctor->id === (int) $actorUserId) {
            return;
        }

        $date = Carbon::parse((string) $appointment->appointment_date);

        $this->notificationService->createNotification([
            'user_id' => $appointment->doctor->id,
            'contenu' => sprintf(
                'Nouveau rendez-vous planifie avec %s le %s a %s.',
                $this->patientName($appointment->patient),
                $date->format('d/m/Y'),
                $date->format('H:i')
            ),
        ]);
    }

    private function findDoctor(int $doctorId): Doctor
    {
        $doctor = Doctor::with(['user', 'cabinet'])->find($doctorId);

        if (! $doctor) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Le medecin selectionne est introuvable.',
            ]);
        }

        return $doctor;
    }

    private function findPatient(int $patientId): Patient
    {
        $patient = Patient::with('user')->find($patientId);

        if (! $patient) {
            throw ValidationException::withMessages([
                'patient_id' => 'Le patient selectionne est introuvable.',
            ]);
        }

        return $patient;
    }

    private function patientName(?Patient $patient): string
    {
        $name = trim(($patient?->user?->prenom ?? '') . ' ' . ($patient?->user?->nom ?? ''));

        return $name !== '' ? $name : 'Patient #' . ($patient?->id ?? '--');
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
