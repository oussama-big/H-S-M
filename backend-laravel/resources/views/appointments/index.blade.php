@extends('layouts.app')

@section('title', 'Mes Rendez-vous')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Calendrier des Rendez-vous</h2>
    @if(auth()->user()->role === 'PATIENT')
        <a href="{{ route('appointments.create') }}" class="btn btn-primary">Prendre RDV</a>
    @endif
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th>Date & Heure</th>
                    <th>{{ auth()->user()->role === 'MEDECIN' ? 'Patient' : 'Médecin' }}</th>
                    <th>Motif</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $app)
                <tr class="align-middle">
                    <td class="fw-bold">{{ \Carbon\Carbon::parse($app->appointment_date)->format('d/m/Y H:i') }}</td>
                    <td>
                        @if(auth()->user()->role === 'MEDECIN')
                            {{ $app->patient->user->nom }} {{ $app->patient->user->prenom }}
                        @else
                            Dr. {{ $app->doctor->user->nom }} ({{ $app->doctor->specialization }})
                        @endif
                    </td>
                    <td><small class="text-muted">{{ Str::limit($app->reason, 30) }}</small></td>
                    <td>
                        @php $color = ['PREVU'=>'info', 'CONFIRME'=>'primary', 'COMPLETE'=>'success', 'ANNULE'=>'danger'][$app->status] ?? 'secondary'; @endphp
                        <span class="badge bg-{{ $color }}">{{ $app->status }}</span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('appointments.show', $app->id) }}" class="btn btn-sm btn-outline-secondary">👁️</a>
                            @if($app->status !== 'ANNULE')
                            <form action="{{ route('appointments.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Annuler ce RDV ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">❌</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">Aucun rendez-vous trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection