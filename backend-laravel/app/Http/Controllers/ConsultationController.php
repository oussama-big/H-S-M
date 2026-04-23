<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Services\DoctorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => Consultation::with(['doctor.user', 'appointment'])->orderBy('date', 'desc')->get(),
            ]);
        }

        $user = Auth::user();

        if ($user->role === 'MEDECIN') {
            $consultations = Consultation::where('doctor_id', $user->doctor->id)
                ->with(['appointment.patient.user'])
                ->orderBy('date', 'desc')
                ->get();
        } else {
            $medicalRecord = $this->doctorService->getPatientMedicalRecord($user->patient->id);
            $consultations = $medicalRecord ? $medicalRecord->consultations : collect();
        }

        return view('consultations.index', compact('consultations'));
    }

    public function create(Request $request)
    {
        $appointment = Appointment::with('patient.dossierMedical')->findOrFail($request->appointment_id);

        return view('consultations.create', compact('appointment'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'required|exists:appointments,id',
            'dossier_medical_id' => 'required|exists:dossier_medicals,id',
            'observations' => 'nullable|string|max:1000',
        ]);

        try {
            $consultation = $this->doctorService->createConsultation($data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $consultation,
                ], 201);
            }

            return redirect()->route('consultations.show', $consultation->id)
                ->with('success', 'Consultation enregistree avec succes.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $consultation = $this->doctorService->getConsultationById($id);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $consultation,
            ]);
        }

        return view('consultations.show', compact('consultation'));
    }

    public function edit($id)
    {
        $consultation = $this->doctorService->getConsultationById($id);

        return view('consultations.show', compact('consultation'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'observations' => 'nullable|string|max:1000',
        ]);

        $consultation = $this->doctorService->updateConsultation($id, $data);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $consultation,
            ]);
        }

        return redirect()->route('consultations.show', $id)->with('success', 'Consultation mise a jour.');
    }

    public function destroy($id)
    {
        try {
            $this->doctorService->deleteConsultation($id);

            return redirect()->route('consultations.index')->with('success', 'Consultation supprimee.');
        } catch (Exception $e) {
            return back()->with('error', 'Echec de la suppression.');
        }
    }

    public function getByPatient($patientId)
    {
        $consultations = Consultation::with(['doctor.user', 'appointment'])
            ->whereHas('appointment', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $consultations,
        ]);
    }
}
