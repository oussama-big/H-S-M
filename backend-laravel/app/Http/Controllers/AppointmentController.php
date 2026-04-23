<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentService;
use App\Services\AppointmentWorkflowService;
use App\Services\DoctorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService;
    private DoctorService $doctorService;
    private AppointmentWorkflowService $appointmentWorkflowService;

    public function __construct(AppointmentService $appointmentService, DoctorService $doctorService, AppointmentWorkflowService $appointmentWorkflowService)
    {
        $this->appointmentService = $appointmentService;
        $this->doctorService = $doctorService;
        $this->appointmentWorkflowService = $appointmentWorkflowService;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $this->appointmentService->getAllAppointments(),
            ]);
        }

        $appointments = collect();

        if (Auth::check() && Auth::user()->role === 'PATIENT' && Auth::user()->patient) {
            $appointments = $this->doctorService->getAppointmentsByPatient(Auth::user()->patient->id);
        }

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->get();

        return view('appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'PATIENT') {
            $patient = Patient::where('id', $user->id)->first();

            if ($patient) {
                $request->merge(['patient_id' => $patient->id]);
            }
        }

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:now',
            'reason' => 'nullable|string|max:500',
        ], [
            'patient_id.required' => 'Veuillez choisir un patient.',
            'patient_id.exists' => 'Le patient selectionne est introuvable.',
            'doctor_id.required' => 'Veuillez choisir un medecin.',
            'doctor_id.exists' => 'Le medecin selectionne est introuvable.',
            'appointment_date.required' => 'Veuillez choisir un creneau horaire.',
            'appointment_date.date' => 'Le format du creneau selectionne est invalide.',
            'appointment_date.after' => 'Le rendez-vous doit etre programme dans le futur.',
        ]);

        try {
            $appointment = $this->appointmentWorkflowService->createAppointment($data, $user?->id);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $appointment,
                ], 201);
            }

            return redirect()->route('backoffice.dashboard')->with('success', 'Rendez-vous enregistre !');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function getByDoctor($doctorId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->appointmentService->getAppointmentsByDoctor($doctorId)->load('patient.user'),
        ]);
    }

    public function getByPatient($patientId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->appointmentService->getAppointmentsByPatient($patientId)->load('doctor.user'),
        ]);
    }

    public function show(Request $request, $id)
    {
        $appointment = $this->appointmentService->getAppointmentById($id);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $appointment,
            ]);
        }

        $appointments = collect([$appointment]);

        return view('appointments.index', compact('appointments'));
    }

    public function edit($id)
    {
        return redirect()->route('appointments.index');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'appointment_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'reason' => 'nullable|string|max:500',
        ]);

        $appointment = $this->appointmentService->updateAppointment($id, $data);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $appointment,
            ]);
        }

        return redirect()->route('appointments.index')->with('success', 'Rendez-vous mis a jour.');
    }

    public function destroy(Request $request, $id)
    {
        $this->appointmentService->deleteAppointment($id);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Rendez-vous supprime.',
            ]);
        }

        return redirect()->route('appointments.index')->with('success', 'Rendez-vous supprime.');
    }
}
