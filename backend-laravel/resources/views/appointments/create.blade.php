@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Prendre un nouveau Rendez-vous</h5>
            </div>
            <div class="card-body">
               <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf

                    @if(auth()->user()->role !== 'PATIENT')
                        <div class="mb-3">
                            <label class="form-label">Choisir le Patient</label>
                            <select name="patient_id" class="form-select" required>
                                @foreach(\App\Models\Patient::all() as $p)
                                    <option value="{{ $p->id }}">{{ $p->user->nom }} {{ $p->user->prenom }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-info small">
                            Réservation pour : <strong>{{ auth()->user()->nom }} {{ auth()->user()->prenom }}</strong>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Médecin</label>
                        <select name="doctor_id" class="form-select" required>
                            @foreach(\App\Models\Doctor::all() as $d)
                                <option value="{{ $d->id }}">Dr. {{ $d->user->nom }} ({{ $d->specialization }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date et Heure</label>
                        <input type="datetime-local" name="appointment_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Motif</label>
                        <textarea name="reason" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Confirmer le Rendez-vous</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection