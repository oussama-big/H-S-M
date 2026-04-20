
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-4 mb-4">
                <div class="text-center mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ $patient->user->nom }}+{{ $patient->user->prenom }}&background=random&size=100" class="rounded-circle" alt="Avatar">
                </div>
                <h4 class="text-center">{{ $patient->user->nom }} {{ $patient->user->prenom }}</h4>
                <p class="text-center text-muted">N° Dossier: {{ $patient->numDossier }}</p>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>📧 Email :</strong> {{ $patient->user->email }}</li>
                    <li class="mb-2"><strong>📞 Tél :</strong> {{ $patient->telephone ?? '---' }}</li>
                    <li class="mb-2"><strong>🎂 Naissance :</strong> {{ $patient->date_of_birth ?? '---' }}</li>
                    <li class="mb-2"><strong>🩸 Sang :</strong> {{ $patient->blood_type ?? '---' }}</li>
                </ul>
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('dossiers.show', $patient->dossierMedical->id) }}" class="btn btn-outline-primary">📁 Voir Dossier Médical</a>
                    <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-outline-secondary btn-sm">Modifier Infos</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Derniers Rendez-vous</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($appointments->take(3) as $app)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y') }}</span>
                                    <small class="text-muted d-block">Motif: {{ Str::limit($app->reason, 40) }}</small>
                                </div>
                                <span class="badge bg-info">{{ $app->status }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun RDV récent.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Historique des Consultations</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Médecin</th>
                                    <th>Diagnostic</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consultations as $cons)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($cons->date)->format('d/m/Y') }}</td>
                                    <td>Dr. {{ $cons->doctor->user->nom }}</td>
                                    <td><small>{{ Str::limit($cons->observations, 30) }}</small></td>
                                    <td><a href="{{ route('consultations.show', $cons->id) }}" class="btn btn-sm btn-link p-0">Détails</a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection