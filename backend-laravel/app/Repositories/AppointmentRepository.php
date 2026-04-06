<?php
namespace App\Repositories;

use App\Models\Appointment;

class AppointmentRepository {
    public function getDoctorAppointments($doctorId) {
        return Appointment::where('doctor_id', $doctorId)
            ->with('patient.user')
            ->orderBy('appointment_date', 'asc')
            ->get();
    }

    public function getPatientAppointments($patientId) {
        return Appointment::where('patient_id', $patientId)
            ->with('doctor.user')
            ->get();
    }
    
}