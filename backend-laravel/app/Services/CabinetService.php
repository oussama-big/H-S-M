<?php

namespace App\Services;

use App\Models\Cabinet;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CabinetService
{
    public function createCabinet(array $data)
    {
        return Cabinet::create([
            'nom' => $data['nom'],
            'adresse' => $data['adresse'],
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    public function getCabinetById($cabinetId)
    {
        $cabinet = Cabinet::find($cabinetId);

        if (! $cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet;
    }

    public function getAllCabinets()
    {
        return Cabinet::all();
    }

    public function getCabinetsByName($name)
    {
        return Cabinet::where('nom', 'like', '%' . $name . '%')->get();
    }

    public function getCabinetsByCity($city)
    {
        return Cabinet::where('adresse', 'like', '%' . $city . '%')->get();
    }

    public function updateCabinet($cabinetId, array $data)
    {
        $cabinet = Cabinet::find($cabinetId);

        if (! $cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        $cabinetData = array_filter([
            'nom' => $data['nom'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
        ], fn ($value) => $value !== null);

        if (! empty($cabinetData)) {
            $cabinet->update($cabinetData);
        }

        return $cabinet;
    }

    public function getCabinetDoctors($cabinetId)
    {
        $cabinet = Cabinet::with('doctors.user')->find($cabinetId);

        if (! $cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet->doctors;
    }

    public function getCabinetInfo($cabinetId)
    {
        $cabinet = Cabinet::with('doctors.user')->find($cabinetId);

        if (! $cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return [
            'id' => $cabinet->id,
            'nom' => $cabinet->nom,
            'adresse' => $cabinet->adresse,
            'telephone' => $cabinet->telephone,
            'email' => $cabinet->email,
            'doctors_count' => $cabinet->doctors->count(),
            'doctors' => $cabinet->doctors,
            'created_at' => $cabinet->created_at,
        ];
    }

    public function deleteCabinet($cabinetId)
    {
        $cabinet = Cabinet::find($cabinetId);

        if (! $cabinet) {
            throw new ModelNotFoundException('Cabinet not found');
        }

        return $cabinet->delete();
    }
}
