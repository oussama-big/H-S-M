<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ordonnance #{{ $ordonnance->id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info-section { margin-top: 20px; width: 100%; }
        .doctor-info { width: 50%; float: left; }
        .patient-info { width: 50%; float: right; text-align: right; }
        .clear { clear: both; }
        .details { margin-top: 50px; padding: 20px; min-height: 300px; border: 1px solid #eee; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CABINET MÉDICAL - MARRAKECH</h2>
    </div>

    <div class="info-section">
        <div class="doctor-info">
            <strong>Dr. {{ $ordonnance->consultation->doctor->name }}</strong><br>
            {{ $ordonnance->consultation->doctor->specialization }}<br>
        </div>
        <div class="patient-info">
            <strong>Patient :</strong> {{ $ordonnance->consultation->dossierMedical->patient->user->name }}<br>
            <strong>Date :</strong> {{ \Carbon\Carbon::parse($ordonnance->date)->format('d/m/Y') }}
        </div>
    </div>

    <div class="clear"></div>

    <div class="details">
        <h3 style="text-decoration: underline;">Prescription :</h3>
        <p>{!! nl2br(e($ordonnance->details)) !!}</p>
    </div>

    <div class="footer">
        Faculté des Sciences Semlalia - Projet HSM System - {{ date('Y') }}
    </div>
</body>
</html>