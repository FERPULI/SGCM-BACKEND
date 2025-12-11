<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaConfirmadaMail extends Mailable
{
    use Queueable, SerializesModels;

    // 👇 ¡ESTA LÍNEA FALTABA! 👇
    public $cita; 

    /**
     * Create a new message instance.
     */
    public function __construct(Cita $cita)
    {
        // Asignamos la cita que recibimos a la propiedad pública
        $this->cita = $cita;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ ¡Tu Cita Médica ha sido Confirmada!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.cita_confirmada',
            with: [
                'cita' => $this->cita,
                // Concatenamos nombres para evitar errores si no existe 'name'
                'paciente' => $this->cita->paciente->user->nombre . ' ' . $this->cita->paciente->user->apellidos,
                'medico' => $this->cita->medico->user->nombre . ' ' . $this->cita->medico->user->apellidos,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}