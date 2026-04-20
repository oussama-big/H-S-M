<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Statistiques globales pour l'Admin ou Secrétaire
        $stats = [
            'total_patients' => Patient::count(),
            'total_doctors' => Doctor::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            'pending_appointments' => Appointment::where('status', 'PREVU')->count(),
        ];

        // Activités récentes (5 dernières consultations)
        $recent_activities = Consultation::with(['doctor.user', 'appointment.patient.user'])
            ->latest()
            ->take(5)
            ->get();

        // Si l'utilisateur est un MÉDECIN, on filtre ses propres stats
        if ($user->role === 'MEDECIN') {
            $stats['my_appointments'] = Appointment::where('doctor_id', $user->doctor->id)
                ->where('status', 'PREVU')->count();
            $stats['my_consultations'] = Consultation::where('doctor_id', $user->doctor->id)->count();
        }

        return view('dashboard', compact('stats', 'recent_activities'));
    }
}