<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Notifications\AppointmentReminder;


#[Signature('app:send-rdv-reminders')]
#[Description('Command description')]
class SendRdvReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
{
    // 1. Chercher les RDV de demain (2026-04-24 car on est le 23)
    $tomorrow = now()->addDay()->format('Y-m-d');
    
    $appointments = Appointment::whereDate('appointment_date', $tomorrow)
                                ->where('status', 'PREVU')
                                ->get();

    if ($appointments->isEmpty()) {
        $this->info("Aucun rendez-vous confirmé pour demain ($tomorrow).");
        return;
    }

    foreach ($appointments as $appointment) {
        // On envoie la notification au patient
        // Assure-toi que la relation "patient" est définie dans ton Model Appointment
        $appointment->patient->notify(new AppointmentReminder($appointment));
    }

    $this->info(count($appointments) . ' rappels envoyés !');
}
}
