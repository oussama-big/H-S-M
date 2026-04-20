@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="mb-3">
                    <img src="https://ui-avatars.com/api/?name=Dr+{{ $doctor->user->nom }}&background=0D6EFD&color=fff&size=128" class="rounded-circle shadow-sm" alt="Avatar">
                </div>
                <h4>Dr. {{ $doctor->user->nom }} {{ $doctor->user->prenom }}</h4>
                <p class="text-primary fw-bold">{{ $doctor->specialization }}</p>
                <hr>
                <div class="text-start">
                    <p><strong>📧 Email :</strong> {{ $doctor->user->email }}</p>
                    <p><strong>📞 Tél :</strong> {{ $doctor->user->telephone ?? 'Non renseigné' }}</p>
                    <p><strong>🆔 Licence :</strong> {{ $doctor->license_number }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card bg-primary text-white shadow-sm border-0">
                        <div class="card-body">
                            <h5>Rendez-vous</h5>
                            <p class="display-6">{{ $doctor->appointments->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card bg-success text-white shadow-sm border-0">
                        <div class="card-body">
                            <h5>Consultations</h5>
                            <p class="display-6">{{ $doctor->consultations->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Dernières Activités</div>
                <div class="card-body">
                    <p class="text-muted">Historique des consultations récentes ici...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection