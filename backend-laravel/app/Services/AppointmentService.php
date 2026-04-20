<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AppointmentService
{
    // =============================
    // APPOINTMENT MANAGEMENT
    // =============================

    public function createAppointment(array $data)
    {
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
        
        if (!$appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        return $appointment;
    }

    public function getAllAppointments()
    {
        return Appointment::with('patient', 'doctor')->get();
    }

    public function getAppointmentsByDoctor($doctorId)
    {
        return Appointment::where('doctor_id', $doctorId)
            ->with('patient')
            ->get();
    }

    public function getAppointmentsByPatient($patientId)
    {
        return Appointment::where('patient_id', $patientId)
            ->with('doctor')
            ->get();
    }

    public function getAppointmentsByStatus($status)
    {
        return Appointment::where('status', $status)
            ->with('patient', 'doctor')
            ->get();
    }

    public function getUpcomingAppointments()
    {
        return Appointment::where('appointment_date', '>', now())
            ->where('status', 'PREVU')
            ->with('patient', 'doctor')
            ->orderBy('appointment_date', 'asc')
            ->get();
    }

    public function updateAppointment($appointmentId, array $data)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        $appointment->update($data);
        return $appointment;
    }

    public function cancelAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        $appointment->update(['status' => 'ANNULE']);
        return $appointment;
    }

    public function completeAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        $appointment->update(['status' => 'COMPLETE']);
        return $appointment;
    }

    public function deleteAppointment($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            throw new ModelNotFoundException('Appointment not found');
        }

        return $appointment->delete();
    }
}
