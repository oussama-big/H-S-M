@extends('layouts.app')

@section('title', 'Historique des Consultations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Historique des Consultations</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>{{ auth()->user()->role === 'MEDECIN' ? 'Patient' : 'Médecin' }}</th>
                        <th>Observations</th>
                        <th>Ordonnance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultations as $consultation)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($consultation->date)->format('d/m/Y') }}</td>
                        <td>
                            @if(auth()->user()->role === 'MEDECIN')
                                {{ $consultation->appointment->patient->user->nom }} {{ $consultation->appointment->patient->user->prenom }}
                            @else
                                Dr. {{ $consultation->doctor->user->nom }}
                            @endif
                        </td>
                        <td>{{ Str::limit($consultation->observations, 50) }}</td>
                        <td>
                            @if($consultation->ordonnance)
                                <span class="badge bg-success">Disponible</span>
                            @else
                                <span class="badge bg-secondary">Aucune</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('consultations.show', $consultation->id) }}" class="btn btn-sm btn-info text-white">Voir</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection