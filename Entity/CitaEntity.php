<?php

class CitaEntity extends BaseEntity
{
    public ?int    $id               = null;
    public ?int    $cliente_id       = null;
    public ?int    $servicio_id      = null;
    public ?int    $user_id          = null;
    public ?string $fecha            = null;
    public ?string $hora_inicio      = null;
    public ?int    $duracion         = 60;
    public ?float  $precio           = 0;
    public ?string $estado           = 'Pendiente';
    public ?string $nota             = null;
    public ?string $created_at       = null;
    public ?string $updated_at       = null;

    // JOINs
    public ?string $cliente_nombre   = null;
    public ?string $cliente_telefono = null;
    public ?string $servicio_nombre  = null;
    public ?string $empleado_nombre  = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    public function getFechaFormateada(): string
    {
        if (!$this->fecha) return '—';
        return date('d/m/Y', strtotime($this->fecha));
    }

    public function getHoraFormateada(): string
    {
        if (!$this->hora_inicio) return '—';
        return date('h:i A', strtotime($this->hora_inicio));
    }

    public function getHoraFin(): string
    {
        if (!$this->hora_inicio) return '—';
        $fin = strtotime($this->hora_inicio) + ($this->duracion * 60);
        return date('H:i', $fin);
    }

    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format((float)$this->precio, 2);
    }

    public function getBadgeEstado(): string
    {
        return match($this->estado) {
            'Pendiente'  => 'bg-warning text-dark',
            'Confirmada' => 'bg-primary',
            'Completada' => 'bg-success',
            'Cancelada'  => 'bg-danger',
            default      => 'bg-secondary',
        };
    }

    public function getIconoEstado(): string
    {
        return match($this->estado) {
            'Pendiente'  => 'fas fa-clock',
            'Confirmada' => 'fas fa-check-circle',
            'Completada' => 'fas fa-check-double',
            'Cancelada'  => 'fas fa-times-circle',
            default      => 'fas fa-question-circle',
        };
    }

    // Mensaje WhatsApp para notificar al cliente
    public function getMensajeWhatsApp(): string
    {
        $nombre  = $this->cliente_nombre  ?? 'Cliente';
        $servicio= $this->servicio_nombre ?? 'servicio';
        $fecha   = $this->getFechaFormateada();
        $hora    = $this->getHoraFormateada();

        return match($this->estado) {
            'Confirmada' => "Hola {$nombre} 👋\nTu cita de *{$servicio}* ha sido *confirmada* ✅\n\n📅 Fecha: {$fecha}\n⏰ Hora: {$hora}\n\n¡Te esperamos en Ana Marcol Makeup Studio! 💄",
            'Cancelada'  => "Hola {$nombre} 👋\nLamentamos informarte que tu cita de *{$servicio}* del {$fecha} a las {$hora} fue *cancelada*.\n\nPuedes reagendar escribiéndonos al 9987-3125.",
            'Completada' => "Hola {$nombre} 👋\n¡Gracias por tu visita a Ana Marcol Makeup Studio! 💄\nEsperamos que hayas disfrutado tu {$servicio}. ¡Hasta la próxima! ❤️",
            default      => "Hola {$nombre}, actualización sobre tu cita de {$servicio} el {$fecha} a las {$hora}.",
        };
    }

    public function getWhatsAppUrl(): string
    {
        $numero  = preg_replace('/[^0-9]/', '', $this->cliente_telefono ?? '');
        if (empty($numero)) return '#';
        $mensaje = $this->getMensajeWhatsApp();
        return "https://wa.me/504{$numero}?text=" . urlencode($mensaje);
    }

    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->servicio_id)) $this->addError('El servicio es obligatorio.');
        if (empty($this->fecha))       $this->addError('La fecha es obligatoria.');
        if (empty($this->hora_inicio)) $this->addError('La hora es obligatoria.');

        return !$this->hasErrors();
    }
}