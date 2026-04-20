@extends('layouts.app')

@section('content')
<div class="container d-flex flex-column align-items-center">
    <div class="card border-1 shadow-sm p-5" id="ordonnance-print" style="width: 21cm; min-height: 29.7cm; background: white;">
        
        <div class="row mb-5 border-bottom pb-3">
            <div class="col-6">
                <h3 class="text-primary mb-0">Dr. {{ $ordonnance->consultation->doctor->user->nom }}</h3>
                <p class="text-muted mb-0">{{ $ordonnance->consultation->doctor->specialization }}</p>
                <small>Licence N°: {{ $ordonnance->consultation->doctor->license_number }}</small>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold">HMS CLINIC</h4>
                <p class="small text-muted mb-0">Faculté des Sciences Semlalia Marrakech</p>
                <p class="small text-muted">Contact: +212 5XX-XXXXXX</p>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <div>
                <span class="text-muted">Patient :</span>
                <h5 class="fw-bold">{{ $ordonnance->consultation->appointment->patient->user->nom }} {{ $ordonnance->consultation->appointment->patient->user->prenom }}</h5>
            </div>
            <div class="text-end">
                <span class="text-muted">Fait le :</span>
                <h5 class="fw-bold">{{ \Carbon\Carbon::parse($ordonnance->date)->format('d/m/Y') }}</h5>
            </div>
        </div>

        <div class="my-5" style="min-height: 400px;">
            <h5 class="text-decoration-underline mb-4">ORDONNANCE :</h5>
            <div style="white-space: pre-line; font-size: 1.1rem; line-height: 1.8;">
                {{ $ordonnance->details }}
            </div>
        </div>

        <div class="mt-auto pt-5 text-end">
            <p class="mb-5 text-muted small">Cachet et Signature du médecin</p>
            <div class="border-top d-inline-block pt-2" style="width: 200px;"></div>
        </div>
    </div>

    <div class="mt-4 d-print-none mb-5">
        <button onclick="window.print()" class="btn btn-dark shadow-sm px-4">🖨️ Imprimer l'Ordonnance</button>
        <a href="{{ route('consultations.show', $ordonnance->consultation_id) }}" class="btn btn-outline-secondary px-4">Retour</a>
    </div>
</div>

<style>
@media print {
    .d-print-none, .sidebar, .navbar { display: none !important; }
    main { margin-left: 0 !important; padding: 0 !important; }
    body { background: white !important; }
    #ordonnance-print { border: none !important; box-shadow: none !important; width: 100% !important; margin: 0 !important; }
}
</style>
@endsection