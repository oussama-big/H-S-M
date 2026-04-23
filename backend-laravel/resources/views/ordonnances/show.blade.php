<!-- @extends('layouts.app')

@section('content')
<div class="container d-flex flex-column align-items-center mt-4">
    <div class="card border-1 shadow-sm p-5" id="ordonnance-print" style="width: 21cm; min-height: 29.7cm; background: white;">
        
        <div class="row mb-5 border-bottom pb-3">
            <div class="col-6">
                <h3 class="text-primary mb-0">
                    Dr. {{ $ordonnance->consultation->doctor->user->nom ?? 'Nom Inconnu' }} 
                    {{ $ordonnance->consultation->doctor->user->prenom ?? '' }}
                </h3>
                <p class="text-muted mb-0">{{ $ordonnance->consultation->doctor->specialization ?? 'Médecin' }}</p>
                <small class="text-secondary">Licence N°: {{ $ordonnance->consultation->doctor->license_number ?? 'N/A' }}</small>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold text-uppercase">HMS Clinic</h4>
                <p class="small text-muted mb-0">Faculté des Sciences Semlalia, Marrakech</p>
                <p class="small text-muted mb-0">Contact: +212 524-XXXXXX</p>
                <p class="small text-muted">Email: contact@hms-clinic.ma</p>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-5">
            <div>
                <span class="text-muted">Patient :</span>
                <h5 class="fw-bold">
                    {{ $ordonnance->consultation->appointment->patient->user->nom ?? 'Patient' }} 
                    {{ $ordonnance->consultation->appointment->patient->user->prenom ?? 'Inconnu' }}
                </h5>
                <small class="text-muted">ID Patient: #{{ $ordonnance->consultation->appointment->patient_id }}</small>
            </div>
            <div class="text-end">
                <span class="text-muted">Fait à Marrakech, le :</span>
                <h5 class="fw-bold">{{ \Carbon\Carbon::parse($ordonnance->date)->format('d/m/Y') }}</h5>
            </div>
        </div>

        <div class="my-5" style="min-height: 500px;">
            <h5 class="text-decoration-underline mb-4 fw-bold" style="letter-spacing: 1px;">ORDONNANCE :</h5>
            <div class="prescription-content" style="white-space: pre-line; font-size: 1.15rem; line-height: 1.8; color: #333;">
                {{ $ordonnance->details }}
            </div>
        </div>

        <div class="mt-auto pt-5 text-end">
            <p class="mb-5 text-muted small italic">Cachet et Signature du médecin</p>
            <div class="border-top d-inline-block pt-2" style="width: 250px; border-top: 2px solid #000 !important;"></div>
        </div>
    </div>

    <div class="mt-4 d-print-none mb-5 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-dark shadow-sm px-4">
            <i class="fas fa-print me-2"></i> Imprimer (Navigateur)
        </button>

        <a href="{{ route('ordonnances.pdf', $ordonnance->id) }}" class="btn btn-primary shadow-sm px-4" target="_blank">
            <i class="fas fa-file-pdf me-2"></i> Générer PDF
        </a>

        <!-- <a href="{{ route('consultations.show', $ordonnance->consultation_id) }}" class="btn btn-outline-secondary px-4">
            Retour à la consultation
        </a> -->
    </div>
</div>

<style>
/* Style spécifique pour l'impression papier */
@media print {
    /* Cache tout ce qui n'est pas l'ordonnance */
    .d-print-none, .sidebar, .navbar, .footer, .btn, .alert { 
        display: none !important; 
    }
    
    /* Supprime les marges par défaut du navigateur */
    @page {
        margin: 0;
        size: auto;
    }

    body { 
        background: white !important; 
        margin: 0;
        padding: 0;
    }

    /* Force l'affichage de la carte sur toute la page */
    #ordonnance-print { 
        border: none !important; 
        box-shadow: none !important; 
        width: 100% !important; 
        margin: 0 !important;
        padding: 2cm !important; /* Marge interne pour l'impression */
    }

    main { 
        margin: 0 !important; 
        padding: 0 !important; 
    }
}

/* Style pour l'écran */
#ordonnance-print {
    font-family: 'Times New Roman', Times, serif; /* Plus formel pour une ordonnance */
}

.prescription-content {
    font-style: italic; /* Donne un aspect "écrit" à la prescription */
}
</style>
@endsection -->