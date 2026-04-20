<?php
namespace App\Services;

use App\Models\Consultation;
use App\Models\Ordonnance;
use App\Models\DossierMedical;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PdfGeneratorService {

    /**
     * Generate consultation PDF content
     */
    public function generateConsultationPdf($consultationId) {
        $consultation = Consultation::with('doctor.user', 'appointment.patient.user', 'dossierMedical')->find($consultationId);
        
        if (!$consultation) {
            throw new ModelNotFoundException('Consultation not found');
        }

        $htmlContent = $this->generateConsultationHtml($consultation);
        
        return [
            'content' => $htmlContent,
            'filename' => 'consultation_' . $consultationId . '.pdf',
            'data' => [
                'consultation_id' => $consultation->id,
                'doctor' => $consultation->doctor->user->nom . ' ' . $consultation->doctor->user->prenom,
                'patient' => $consultation->appointment->patient->user->nom . ' ' . $consultation->appointment->patient->user->prenom,
                'date' => $consultation->date,
                'observations' => $consultation->observations,
            ]
        ];
    }

    /**
     * Generate ordonnance PDF content
     */
    public function generateOrdonnancePdf($ordonnanceId) {
        $ordonnance = Ordonnance::with('consultation.doctor.user', 'consultation.dossierMedical.patient.user')->find($ordonnanceId);
        
        if (!$ordonnance) {
            throw new ModelNotFoundException('Ordonnance not found');
        }

        $htmlContent = $this->generateOrdonnanceHtml($ordonnance);
        
        return [
            'content' => $htmlContent,
            'filename' => 'ordonnance_' . $ordonnanceId . '.pdf',
            'data' => [
                'ordonnance_id' => $ordonnance->id,
                'doctor' => $ordonnance->consultation->doctor->user->nom . ' ' . $ordonnance->consultation->doctor->user->prenom,
                'patient' => $ordonnance->consultation->dossierMedical->patient->user->nom . ' ' . $ordonnance->consultation->dossierMedical->patient->user->prenom,
                'date' => $ordonnance->date,
                'details' => $ordonnance->details,
            ]
        ];
    }

    /**
     * Generate dossier medical PDF content
     */
    public function generateDossierPdf($dossierId) {
        $dossier = DossierMedical::with('patient.user', 'consultations.doctor.user', 'consultations.ordonnance')->find($dossierId);
        
        if (!$dossier) {
            throw new ModelNotFoundException('Dossier medical not found');
        }

        $htmlContent = $this->generateDossierHtml($dossier);
        
        return [
            'content' => $htmlContent,
            'filename' => 'dossier_medical_' . $dossierId . '.pdf',
            'data' => [
                'dossier_id' => $dossier->id,
                'patient' => $dossier->patient->user->nom . ' ' . $dossier->patient->user->prenom,
                'consultations_count' => $dossier->consultations->count(),
                'diagnosis' => $dossier->diagnosis,
                'treatment_plan' => $dossier->treatment_plan,
            ]
        ];
    }

    /**
     * Generate HTML for consultation
     */
    private function generateConsultationHtml($consultation) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Consultation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .section { margin: 20px 0; }
        .label { font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>CONSULTATION</h2>
    </div>
    
    <div class="section">
        <p><span class="label">Consultation ID:</span> {$consultation->id}</p>
        <p><span class="label">Date:</span> {$consultation->date}</p>
    </div>
    
    <div class="section">
        <h3>Médecin</h3>
        <p>{$consultation->doctor->user->nom} {$consultation->doctor->user->prenom}</p>
        <p>Spécialité: {$consultation->doctor->specialization}</p>
    </div>
    
    <div class="section">
        <h3>Patient</h3>
        <p>{$consultation->appointment->patient->user->nom} {$consultation->appointment->patient->user->prenom}</p>
        <p>Email: {$consultation->appointment->patient->user->email}</p>
    </div>
    
    <div class="section">
        <h3>Observations</h3>
        <p>{$consultation->observations}</p>
    </div>
    
    <hr>
    <p><small>Généré le: " . date('Y-m-d H:i:s') . "</small></p>
</body>
</html>
HTML;
    }

    /**
     * Generate HTML for ordonnance
     */
    private function generateOrdonnanceHtml($ordonnance) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ordonnance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .section { margin: 20px 0; }
        .label { font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ORDONNANCE</h2>
    </div>
    
    <div class="section">
        <p><span class="label">Ordonnance ID:</span> {$ordonnance->id}</p>
        <p><span class="label">Date:</span> {$ordonnance->date}</p>
    </div>
    
    <div class="section">
        <h3>Médecin</h3>
        <p>{$ordonnance->consultation->doctor->user->nom} {$ordonnance->consultation->doctor->user->prenom}</p>
        <p>License: {$ordonnance->consultation->doctor->license_number}</p>
    </div>
    
    <div class="section">
        <h3>Patient</h3>
        <p>{$ordonnance->consultation->dossierMedical->patient->user->nom} {$ordonnance->consultation->dossierMedical->patient->user->prenom}</p>
    </div>
    
    <div class="section">
        <h3>Détails du traitement</h3>
        <p>{$ordonnance->details}</p>
    </div>
    
    <hr>
    <p><small>Généré le: " . date('Y-m-d H:i:s') . "</small></p>
</body>
</html>
HTML;
    }

    /**
     * Generate HTML for dossier medical
     */
    private function generateDossierHtml($dossier) {
        $consultationsHtml = '';
        foreach ($dossier->consultations as $consultation) {
            $consultationsHtml .= <<<HTML
            <div class="consultation-item">
                <p><strong>Date:</strong> {$consultation->date}</p>
                <p><strong>Médecin:</strong> {$consultation->doctor->user->nom} {$consultation->doctor->user->prenom}</p>
                <p><strong>Observations:</strong> {$consultation->observations}</p>
            </div>
HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dossier Médical</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .section { margin: 20px 0; }
        .label { font-weight: bold; color: #333; }
        .consultation-item { border-left: 3px solid #0066cc; padding-left: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DOSSIER MÉDICAL</h2>
    </div>
    
    <div class="section">
        <p><span class="label">Dossier ID:</span> {$dossier->id}</p>
    </div>
    
    <div class="section">
        <h3>Patient</h3>
        <p><strong>Nom:</strong> {$dossier->patient->user->nom} {$dossier->patient->user->prenom}</p>
        <p><strong>Email:</strong> {$dossier->patient->user->email}</p>
        <p><strong>Groupe sanguin:</strong> {$dossier->patient->blood_type}</p>
    </div>
    
    <div class="section">
        <h3>Diagnostic</h3>
        <p>{$dossier->diagnosis}</p>
    </div>
    
    <div class="section">
        <h3>Plan de traitement</h3>
        <p>{$dossier->treatment_plan}</p>
    </div>
    
    <div class="section">
        <h3>Consultations ({$dossier->consultations->count()})</h3>
        {$consultationsHtml}
    </div>
    
    <hr>
    <p><small>Généré le: " . date('Y-m-d H:i:s') . "</small></p>
</body>
</html>
HTML;
    }
}
