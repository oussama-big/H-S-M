<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use App\Models\DossierMedical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class DossierMedicalController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Affiche le dossier médical (Vue détaillée)
     */
    public function show($id)
    {
        try {
            $dossier = DossierMedical::with(['patient.user', 'consultations.doctor.user', 'consultations.ordonnance'])
                ->findOrFail($id);

            // Sécurité : Un patient ne peut voir que son propre dossier
            if (Auth::user()->role === 'PATIENT' && Auth::user()->patient->id !== $dossier->patient_id) {
                abort(403, 'Accès non autorisé.');
            }

            return view('dossiers.show', compact('dossier'));
        } catch (Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Dossier introuvable.');
        }
    }

    /**
     * Formulaire d'édition (uniquement pour les médecins)
     */
    public function edit($id)
    {
        $dossier = DossierMedical::findOrFail($id);
        return view('dossiers.edit', compact('dossier'));
    }

    /**
     * Mise à jour du diagnostic et du plan de traitement
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'diagnosis' => 'nullable|string|max:1000',
            'treatment_plan' => 'nullable|string|max:2000',
        ]);

        try {
            $this->doctorService->updateDossierMedical($id, $data);
            return redirect()->route('dossiers.show', $id)->with('success', 'Dossier médical mis à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Accès rapide pour le patient connecté
     */
    public function myRecord()
    {
        $patientId = Auth::user()->patient->id;
        $dossier = $this->doctorService->getPatientMedicalRecord($patientId);

        if (!$dossier) {
            return redirect()->route('dashboard')->with('error', 'Aucun dossier médical trouvé.');
        }

        return redirect()->route('dossiers.show', $dossier->id);
    }
}