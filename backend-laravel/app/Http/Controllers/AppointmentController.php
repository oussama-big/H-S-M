<?php
// app/Http/Controllers/AppointmentController.php

namespace App\Http\Controllers;

use App\Services\AppointmentService; // Utilise le bon service
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService; // Change ici
    private DoctorService $doctorService;

    public function __construct(AppointmentService $appointmentService, DoctorService $doctorService)
    {
        $this->appointmentService = $appointmentService;
        $this->doctorService = $doctorService;
    }
    // app/Http/Controllers/AppointmentController.php

    public function create()
    {
        // Vérifie bien que ce code est présent exactement comme ça
        $doctors = \App\Models\Doctor::with('user')->get();
        return view('appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // On s'assure d'avoir l'ID de la table 'patients'
        if ($user->role === 'PATIENT') {
            // On récupère l'ID réel de la ligne dans la table patients
            $patient = \App\Models\Patient::where('id', $user->id)->first();
            if ($patient) {
                $request->merge(['patient_id' => $patient->id]);
            }
        }

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ]);

        // DEBUG : Si tu ne vois pas ce tableau s'afficher, c'est que la validation bloque
        // dd($data); 

        try {
            $this->doctorService->createAppointment($data);
            return redirect()->route('dashboard')->with('success', 'Rendez-vous enregistré !');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }   // en remplaçant $this->doctorService par $this->appointmentService
}