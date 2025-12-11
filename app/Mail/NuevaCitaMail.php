<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaCitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cita; // Propiedad pública para acceder a los datos en la vista

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Cita Médica ha sido Registrada',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.nueva_cita',
            with: [
                'cita' => $this->cita,
                // CORRECCIÓN: Usar 'nombre' y 'apellidos' o 'nombre_completo'
                'paciente' => $this->cita->paciente->user->nombre . ' ' . $this->cita->paciente->user->apellidos,
                'medico' => $this->cita->medico->user->nombre . ' ' . $this->cita->medico->user->apellidos,
            ]
        );
    }
}