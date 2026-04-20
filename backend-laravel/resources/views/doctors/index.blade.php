@extends('layouts.app')

@section('title', 'Gestion des Médecins')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Corps Médical</h2>
    <a href="{{ route('doctors.create') }}" class="btn btn-primary">➕ Ajouter un Médecin</a>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Médecin</th>
                    <th>Spécialité</th>
                    <th>N° Licence</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($doctors as $doctor)
                <tr class="align-middle">
                    <td>
                        <div class="fw-bold">Dr. {{ $doctor->user->nom }} {{ $doctor->user->prenom }}</div>
                        <small class="text-muted">{{ $doctor->user->email }}</small>
                    </td>
                    <td><span class="badge bg-info text-dark">{{ $doctor->specialization }}</span></td>
                    <td><code>{{ $doctor->license_number }}</code></td>
                    <td>{{ $doctor->user->telephone ?? '---' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('doctors.show', $doctor->id) }}" class="btn btn-sm btn-outline-info">👁️</a>
                            <a href="{{ route('doctors.edit', $doctor->id) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                            <form action="{{ route('doctors.destroy', $doctor->id) }}" method="POST" onsubmit="return confirm('Supprimer ce médecin ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection