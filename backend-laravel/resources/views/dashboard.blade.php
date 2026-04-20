@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Tableau de Bord</h2>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm border-0 mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small">Total Patients</h6>
                        <h2 class="mb-0">{{ $stats['total_patients'] }}</h2>
                    </div>
                    <div class="fs-1 opacity-50">👥</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow-sm border-0 mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small">RDV Aujourd'hui</h6>
                        <h2 class="mb-0">{{ $stats['today_appointments'] }}</h2>
                    </div>
                    <div class="fs-1 opacity-50">📅</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark shadow-sm border-0 mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small">Médecins Actifs</h6>
                        <h2 class="mb-0">{{ $stats['total_doctors'] }}</h2>
                    </div>
                    <div class="fs-1 opacity-50">👨‍⚕️</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow-sm border-0 mb-4">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-uppercase small">RDV en Attente</h6>
                        <h2 class="mb-0">{{ $stats['pending_appointments'] }}</h2>
                    </div>
                    <div class="fs-1 opacity-50">⏳</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Dernières Consultations</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_activities as $activity)
                            <tr>
                                <td>{{ $activity->appointment->patient->user->nom }}</td>
                                <td>Dr. {{ $activity->doctor->user->nom }}</td>
                                <td>{{ \Carbon\Carbon::parse($activity->date)->format('d/m/H:i') }}</td>
                                <td><a href="{{ route('consultations.show', $activity->id) }}" class="btn btn-sm btn-link">Voir</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Actions Rapides</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('appointments.create') }}" class="btn btn-outline-primary text-start">🗓️ Nouveau Rendez-vous</a>
                        <a href="{{ route('patients.create') }}" class="btn btn-outline-success text-start">➕ Ajouter un Patient</a>
                        <a href="{{ route('ordonnances.create') }}" class="btn btn-outline-info text-start">💊 Rédiger Ordonnance</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection