@extends('layouts.app')

@section('title', 'Dossier Médical')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📂 Dossier Médical : {{ $dossier->patient->user->nom }} {{ $dossier->patient->user->prenom }}</h2>
        @if(auth()->user()->role === 'MEDECIN')
            <a href="{{ route('dossiers.edit', $dossier->id) }}" class="btn btn-warning">Modifier le diagnostic</a>
        @endif
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">Informations Patient</div>
                <div class="card-body">
                    <p><strong>N° Dossier :</strong> <span class="badge bg-secondary">#{{ $dossier->id }}</span></p>
                    <p><strong>Email :</strong> {{ $dossier->patient->user->email }}</p>
                    <p><strong>Téléphone :</strong> {{ $dossier->patient->user->telephone ?? 'N/A' }}</p>
                    <p><strong>Créé le :</strong> {{ $dossier->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold text-primary">État de Santé Actuel</div>
                <div class="card-body">
                    <h6>Diagnostic principal :</h6>
                    <p class="text-dark bg-light p-2 rounded">{{ $dossier->diagnosis ?? 'Aucun diagnostic enregistré.' }}</p>
                    
                    <h6 class="mt-4">Plan de traitement :</h6>
                    <p class="text-dark bg-light p-2 rounded">{{ $dossier->treatment_plan ?? 'Aucun plan de traitement défini.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">Historique des Consultations ({{ $dossier->consultations->count() }})</div>
        <div class="card-body">
            <div class="list-group">
                @forelse($dossier->consultations->sortByDesc('date') as $cons)
                    <div class="list-group-item list-group-item-action p-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Consultation avec Dr. {{ $cons->doctor->user->nom }}</h6>
                            <small class="text-primary fw-bold">{{ \Carbon\Carbon::parse($cons->date)->format('d/m/Y') }}</small>
                        </div>
                        <p class="mb-1 text-muted small">{{ Str::limit($cons->observations, 150) }}</p>
                        @if($cons->ordonnance)
                            <small class="text-success fw-bold">✅ Ordonnance délivrée</small>
                        @endif
                        <div class="mt-2 text-end">
                            <a href="{{ route('consultations.show', $cons->id) }}" class="btn btn-sm btn-link p-0">Voir les détails</a>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-3">Aucune consultation enregistrée.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection