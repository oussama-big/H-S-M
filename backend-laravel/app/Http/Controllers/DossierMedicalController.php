<?php

namespace App\Http\Controllers;

use App\Models\DossierMedical;
use App\Services\DoctorService;
use App\Services\DossierMedicalService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DossierMedicalController extends Controller
{
    private DoctorService $doctorService;
    private DossierMedicalService $dossierMedicalService;

    public function __construct(DoctorService $doctorService, DossierMedicalService $dossierMedicalService)
    {
        $this->doctorService = $doctorService;
        $this->dossierMedicalService = $dossierMedicalService;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dossierMedicalService->getAllDossiers(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'diagnosis' => 'nullable|string|max:1000',
            'treatment_plan' => 'nullable|string|max:2000',
        ]);

        $dossier = $this->dossierMedicalService->createDossierMedical($data);

        return response()->json([
            'success' => true,
            'message' => 'Dossier medical cree avec succes.',
            'data' => $dossier,
        ], 201);
    }

    /**
     * Affiche le dossier medical (Vue detaillee)
     */
    public function show(Request $request, $id)
    {
        try {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $this->dossierMedicalService->getDossierMedicalById($id),
                ]);
            }

            $dossier = DossierMedical::with(['patient.user', 'consultations.doctor.user', 'consultations.ordonnance'])
                ->findOrFail($id);

            if (Auth::user()->role === 'PATIENT' && Auth::user()->patient->id !== $dossier->patient_id) {
                abort(403, 'Acces non autorise.');
            }

            return view('dossiers.show', compact('dossier'));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dossier introuvable.',
            ], 404);
        } catch (Exception $e) {
            return redirect()->route('backoffice.dashboard')->with('error', 'Dossier introuvable.');
        }
    }

    /**
     * Formulaire d'edition (uniquement pour les medecins)
     */
    public function edit($id)
    {
        $dossier = DossierMedical::findOrFail($id);

        return view('dossiers.edit', compact('dossier'));
    }

    /**
     * Mise a jour du diagnostic et du plan de traitement
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'diagnosis' => 'nullable|string|max:1000',
            'treatment_plan' => 'nullable|string|max:2000',
        ]);

        try {
            $this->doctorService->updateDossierMedical($id, $data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dossier medical mis a jour.',
                    'data' => $this->dossierMedicalService->getDossierMedicalById($id),
                ]);
            }

            return redirect()->route('dossiers.show', $id)->with('success', 'Dossier medical mis a jour.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Echec de la mise a jour : ' . $e->getMessage(),
                ], 400);
            }

            return back()->with('error', 'Echec de la mise a jour : ' . $e->getMessage());
        }
    }

    /**
     * Acces rapide pour le patient connecte
     */
    public function myRecord()
    {
        $patientId = Auth::user()->patient->id;
        $dossier = $this->doctorService->getPatientMedicalRecord($patientId);

        if (!$dossier) {
            return redirect()->route('backoffice.dashboard')->with('error', 'Aucun dossier medical trouve.');
        }

        return redirect()->route('dossiers.show', $dossier->id);
    }

    public function destroy($id)
    {
        try {
            $this->dossierMedicalService->deleteDossierMedical($id);

            return response()->json([
                'success' => true,
                'message' => 'Dossier medical supprime.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dossier introuvable.',
            ], 404);
        }
    }

    public function getByPatient($patientId)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->dossierMedicalService->getDossierByPatient($patientId),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dossier medical introuvable pour ce patient.',
            ], 404);
        }
    }

    public function getSummary($patientId)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->dossierMedicalService->getDossierSummary($patientId),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resume du dossier medical introuvable.',
            ], 404);
        }
    }
}
