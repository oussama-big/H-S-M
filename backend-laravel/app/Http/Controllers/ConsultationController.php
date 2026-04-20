<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ConsultationController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Liste des consultations (Historique)
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'MEDECIN') {
            // On peut filtrer les consultations du médecin connecté
            $consultations = \App\Models\Consultation::where('doctor_id', $user->doctor->id)
                ->with(['appointment.patient.user'])
                ->orderBy('date', 'desc')->get();
        } else {
            // Pour le patient, on récupère via son dossier médical
            $medicalRecord = $this->doctorService->getPatientMedicalRecord($user->patient->id);
            $consultations = $medicalRecord ? $medicalRecord->consultations : collect();
        }

        return view('consultations.index', compact('consultations'));
    }

    /**
     * Formulaire pour créer une consultation (souvent depuis un RDV)
     */
    public function create(Request $request)
    {
        $appointment = \App\Models\Appointment::with('patient.dossierMedical')->findOrFail($request->appointment_id);
        return view('consultations.create', compact('appointment'));
    }

    /**
     * Enregistrer la consultation
     */
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
            return redirect()->route('consultations.show', $consultation->id)
                           ->with('success', 'Consultation enregistrée avec succès.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Détails d'une consultation (Vue)
     */
    public function show($id)
    {
        $consultation = $this->doctorService->getConsultationById($id);
        return view('consultations.show', compact('consultation'));
    }

    public function destroy($id)
    {
        try {
            $this->doctorService->deleteConsultation($id);
            return redirect()->route('consultations.index')->with('success', 'Consultation supprimée.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la suppression.');
        }
    }
}