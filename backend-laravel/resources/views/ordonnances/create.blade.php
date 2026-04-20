@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">Rédiger une Ordonnance</h5>
                    <small>Pour la consultation du {{ \Carbon\Carbon::parse($consultation->date)->format('d/m/Y') }}</small>
                </div>
                <div class="card-body">
                    <form action="{{ route('ordonnances.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">

                        <div class="mb-4 text-muted">
                            <strong>Patient :</strong> {{ $consultation->appointment->patient->user->nom }} {{ $consultation->appointment->patient->user->prenom }}<br>
                            <strong>Médecin :</strong> Dr. {{ auth()->user()->nom }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Prescription (Médicaments, doses, durée) :</label>
                            <textarea name="details" class="form-control" rows="10" placeholder="Ex: Paracétamol 500mg, 1 comprimé 3 fois par jour pendant 5 jours..." required></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Générer l'ordonnance</button>
                            <a href="{{ route('consultations.show', $consultation->id) }}" class="btn btn-link text-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection