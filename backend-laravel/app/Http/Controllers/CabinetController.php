<?php

namespace App\Http\Controllers;

use App\Services\CabinetService;
use Exception;
use Illuminate\Http\Request;

class CabinetController extends Controller
{
    private CabinetService $cabinetService;

    public function __construct(CabinetService $cabinetService)
    {
        $this->cabinetService = $cabinetService;
    }

    public function index(Request $request)
    {
        $cabinets = $this->cabinetService->getAllCabinets();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $cabinets,
            ]);
        }

        return view('cabinets.index', compact('cabinets'));
    }

    public function create()
    {
        return view('cabinets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255|unique:cabinets,nom',
            'adresse' => 'required|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $cabinet = $this->cabinetService->createCabinet($data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $cabinet,
                ], 201);
            }

            return redirect()->route('cabinets.index')->with('success', 'Le cabinet a ete cree avec succes.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        $cabinet = $this->cabinetService->getCabinetInfo($id);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $cabinet,
            ]);
        }

        return view('cabinets.show', compact('cabinet'));
    }

    public function edit($id)
    {
        $cabinet = $this->cabinetService->getCabinetById($id);

        return view('cabinets.edit', compact('cabinet'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => 'nullable|string|max:255|unique:cabinets,nom,' . $id,
            'adresse' => 'nullable|string|max:500',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            $cabinet = $this->cabinetService->updateCabinet($id, $data);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $cabinet,
                ]);
            }

            return redirect()->route('cabinets.index')->with('success', 'Cabinet mis a jour.');
        } catch (Exception $e) {
            return back()->with('error', 'Echec de la modification.');
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->cabinetService->deleteCabinet($id);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cabinet supprime.',
                ]);
            }

            return redirect()->route('cabinets.index')->with('success', 'Cabinet supprime.');
        } catch (Exception $e) {
            return back()->with('error', 'Suppression impossible.');
        }
    }

    public function getDoctors($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->cabinetService->getCabinetDoctors($id),
        ]);
    }

    public function searchByName(Request $request)
    {
        $name = $request->query('name', $request->query('q', ''));

        return response()->json([
            'success' => true,
            'data' => $name === '' ? [] : $this->cabinetService->getCabinetsByName($name),
        ]);
    }
}
