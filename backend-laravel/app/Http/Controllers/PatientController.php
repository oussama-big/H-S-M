<?php

namespace App\Http\Controllers;

use App\Services\PatientService;
use Illuminate\Http\Request;
use Exception;

class PatientController extends Controller
{
    private PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Liste de tous les patients (Vue)
     */
    public function index(Request $request)
    {
        // Si une recherche est effectuée
        if ($request->has('q')) {
            $patients = $this->patientService->searchPatients($request->q);
        } else {
            $patients = $this->patientService->getAllPatients();
        }

        return view('patients.index', compact('patients'));
    }

    /**
     * Formulaire d'ajout
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Enregistrement
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'blood_type' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->patientService->registerPatient($data);
            return redirect()->route('patients.index')->with('success', 'Patient enregistré avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Échec de l\'enregistrement.');
        }
    }

    /**
     * Profil détaillé du patient
     */
    public function show($id)
    {
        $patient = $this->patientService->getPatientById($id);
        $consultations = $this->patientService->getPatientConsultationHistory($id);
        $appointments = $this->patientService->getPatientAppointments($id);

        return view('patients.show', compact('patient', 'consultations', 'appointments'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $patient = $this->patientService->getPatientById($id);
        return view('patients.edit', compact('patient'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:M,F,Autre',
            'blood_type' => 'nullable|string|max:10',
            'telephone' => 'nullable|string|max:20',
        ]);

        try {
            $this->patientService->updatePatient($id, $data);
            return redirect()->route('patients.show', $id)->with('success', 'Infos patient mises à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur de mise à jour.');
        }
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        try {
            $this->patientService->deletePatient($id);
            return redirect()->route('patients.index')->with('success', 'Patient supprimé.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}