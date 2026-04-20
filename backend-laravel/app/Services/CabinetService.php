<?php

namespace App\Services;

use App\Models\Cabinet;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabinetService
{
    // =============================
    // CABINET (MEDICAL OFFICE) MANAGEMENT
    // =============================

    public function createCabinet(array $data)
    {
        return Cabinet::create([
            'name' => $data['name'],
            'address' => $data['address'],
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public function getCabinetById($cabinetId)
    {
        $cabinet = Cabinet::with('doctors')->find($cabinetId);
        
        if (!$cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet;
    }

    public function getAllCabinets()
    {
        return Cabinet::with('doctors')->get();
    }

    public function getCabinetsByName($name)
    {
        return Cabinet::where('name', 'like', '%' . $name . '%')
            ->with('doctors')
            ->get();
    }

    public function getCabinetsByCity($city)
    {
        return Cabinet::where('address', 'like', '%' . $city . '%')
            ->with('doctors')
            ->get();
    }

    public function updateCabinet($cabinetId, array $data)
    {
        $cabinet = Cabinet::find($cabinetId);
        
        if (!$cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        $cabinetData = array_filter([
            'name' => $data['name'] ?? null,
            'address' => $data['address'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($cabinetData)) {
            $cabinet->update($cabinetData);
        }

        return $cabinet;
    }

    public function getCabinetDoctors($cabinetId)
    {
        $cabinet = Cabinet::with('doctors.user')->find($cabinetId);
        
        if (!$cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet->doctors;
    }

    public function getCabinetInfo($cabinetId)
    {
        $cabinet = Cabinet::with('doctors')->find($cabinetId);
        
        if (!$cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return [
            'id' => $cabinet->id,
            'name' => $cabinet->name,
            'address' => $cabinet->address,
            'telephone' => $cabinet->telephone,
            'email' => $cabinet->email,
            'doctors_count' => $cabinet->doctors->count(),
            'doctors' => $cabinet->doctors->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->nom . ' ' . $doctor->user->prenom,
                    'specialization' => $doctor->specialization,
                ];
            }),
            'created_at' => $cabinet->created_at,
        ];
    }

    public function deleteCabinet($cabinetId)
    {
        $cabinet = Cabinet::find($cabinetId);
        
        if (!$cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet->delete();
    }
}
