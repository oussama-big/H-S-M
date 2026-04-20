<?php

namespace App\Http\Controllers;

use App\Services\DoctorService;
use Illuminate\Http\Request;
use Exception;

class OrdonnanceController extends Controller
{
    private DoctorService $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Formulaire de création d'ordonnance
     */
    public function create(Request $request)
    {
        // On récupère la consultation pour lier l'ordonnance
        $consultation = $this->doctorService->getConsultationById($request->consultation_id);
        return view('ordonnances.create', compact('consultation'));
    }

    /**
     * Enregistrement de l'ordonnance
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'details' => 'required|string|max:2000',
        ]);

        try {
            $ordonnance = $this->doctorService->createOrdonnance($data);
            return redirect()->route('ordonnances.show', $ordonnance->id)
                           ->with('success', 'Ordonnance créée avec succès.');
        } catch (Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Affichage détaillé de l'ordonnance (Prête à imprimer)
     */
    public function show($id)
    {
        $ordonnance = $this->doctorService->getOrdonnanceById($id);
        return view('ordonnances.show', compact('ordonnance'));
    }

    /**
     * Modification d'une ordonnance
     */
    public function edit($id)
    {
        $ordonnance = $this->doctorService->getOrdonnanceById($id);
        return view('ordonnances.edit', compact('ordonnance'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'details' => 'required|string|max:2000',
        ]);

        try {
            $this->doctorService->updateOrdonnance($id, $data);
            return redirect()->route('ordonnances.show', $id)->with('success', 'Ordonnance mise à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la modification.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->doctorService->deleteOrdonnance($id);
            return redirect()->route('consultations.index')->with('success', 'Ordonnance supprimée.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}