@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Détails de la Consultation #{{ $consultation->id }}</h5>
            <span class="text-muted text-uppercase small">{{ \Carbon\Carbon::parse($consultation->date)->format('d M Y') }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Médecin : <span class="text-primary">Dr. {{ $consultation->doctor->user->nom }}</span></h6>
                    <p class="small text-muted">{{ $consultation->doctor->specialization }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Patient : <strong>{{ $consultation->appointment->patient->user->nom }} {{ $consultation->appointment->patient->user->prenom }}</strong></h6>
                </div>
            </div>

            <div class="p-3 bg-light rounded mb-4">
                <h6>Observations & Diagnostic :</h6>
                <p>{{ $consultation->observations ?? 'Aucune observation enregistrée.' }}</p>
            </div>

            @if($consultation->ordonnance)
            <div class="border p-3 rounded border-success">
                <h6 class="text-success border-bottom pb-2">💊 Ordonnance associée :</h6>
                <p class="mt-2">{{ $consultation->ordonnance->details }}</p>
                <small class="text-muted">Prescrit le : {{ \Carbon\Carbon::parse($consultation->ordonnance->date)->format('d/m/Y') }}</small>
            </div>
            @endif
        </div>
        <div class="card-footer bg-white text-end">
            <a href="{{ route('consultations.index') }}" class="btn btn-secondary">Retour à l'historique</a>
            <button onclick="window.print()" class="btn btn-outline-dark">🖨️ Imprimer</button>
        </div>
    </div>
</div>
@endsection