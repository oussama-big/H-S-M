@extends('layouts.app')

@section('title', 'Gestion des Patients')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Répertoire des Patients</h2>
    <a href="{{ route('patients.create') }}" class="btn btn-primary">➕ Nouveau Patient</a>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('patients.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control" placeholder="Rechercher par nom, email ou N° dossier..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
            @if(request('q'))
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

<div class="card shadow border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>N° Dossier</th>
                    <th>Nom Complet</th>
                    <th>Téléphone</th>
                    <th>Groupe Sanguin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                <tr class="align-middle">
                    <td><span class="badge bg-secondary">#{{ $patient->numDossier }}</span></td>
                    <td>
                        <div class="fw-bold">{{ $patient->user->nom }} {{ $patient->user->prenom }}</div>
                        <small class="text-muted">{{ $patient->user->email }}</small>
                    </td>
                    <td>{{ $patient->telephone ?? '---' }}</td>
                    <td><span class="badge bg-danger">{{ $patient->blood_type ?? 'N/A' }}</span></td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-sm btn-outline-info">👁️ Profil</a>
                            <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-outline-primary">✏️</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4 text-muted">Aucun patient trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection