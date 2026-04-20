<?php

namespace App\Http\Controllers;

use App\Services\CabinetService;
use Illuminate\Http\Request;
use Exception;

class CabinetController extends Controller
{
    private CabinetService $cabinetService;

    public function __construct(CabinetService $cabinetService)
    {
        $this->cabinetService = $cabinetService;
    }

    /**
     * Liste des cabinets (Vue)
     */
    public function index()
    {
        $cabinets = $this->cabinetService->getAllCabinets();
        return view('cabinets.index', compact('cabinets'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('cabinets.create');
    }

    /**
     * Enregistrement d'un nouveau cabinet
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:cabinets,name',
            'address' => 'required|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $this->cabinetService->createCabinet($data);
            return redirect()->route('cabinets.index')->with('success', 'Le cabinet a été créé avec succès.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Détails d'un cabinet (avec ses médecins)
     */
    public function show($id)
    {
        $cabinet = $this->cabinetService->getCabinetInfo($id);
        return view('cabinets.show', compact('cabinet'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $cabinet = $this->cabinetService->getCabinetInfo($id);
        return view('cabinets.edit', compact('cabinet'));
    }

    /**
     * Mise à jour du cabinet
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255|unique:cabinets,name,' . $id,
            'address' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $this->cabinetService->updateCabinet($id, $data);
            return redirect()->route('cabinets.index')->with('success', 'Cabinet mis à jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Échec de la modification.');
        }
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        try {
            $this->cabinetService->deleteCabinet($id);
            return redirect()->route('cabinets.index')->with('success', 'Cabinet supprimé.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }
}