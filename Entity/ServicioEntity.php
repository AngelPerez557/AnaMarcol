<?php

class ServicioEntity extends BaseEntity
{
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $descripcion = null;
    public ?float  $precio_base = 0;
    public ?int    $duracion    = 60;
    public ?int    $activo      = 1;

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format((float)$this->precio_base, 2);
    }

    public function getDuracionFormateada(): string
    {
        if ($this->duracion < 60) return $this->duracion . ' min';
        $horas   = intdiv($this->duracion, 60);
        $minutos = $this->duracion % 60;
        return $minutos > 0
            ? "{$horas}h {$minutos}min"
            : "{$horas}h";
    }

    public function isValid(): bool
    {
        $this->clearErrors();
        if (empty($this->nombre))   $this->addError('El nombre es obligatorio.');
        if (empty($this->duracion)) $this->addError('La duración es obligatoria.');
        return !$this->hasErrors();
    }
}