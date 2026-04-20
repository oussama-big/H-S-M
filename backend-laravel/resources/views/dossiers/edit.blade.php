@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="mb-0">Mise à jour du Dossier : {{ $dossier->patient->user->nom }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('dossiers.update', $dossier->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Diagnostic Global</label>
                        <textarea name="diagnosis" class="form-control" rows="4">{{ old('diagnosis', $dossier->diagnosis) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Plan de Traitement</label>
                        <textarea name="treatment_plan" class="form-control" rows="6">{{ old('treatment_plan', $dossier->treatment_plan) }}</textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="{{ route('dossiers.show', $dossier->id) }}" class="btn btn-link text-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection