<?php

class ComboEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id          = null;
    public ?string $nombre      = null;
    public ?string $descripcion = null;
    public ?string $imagen_url  = null;
    public ?float  $descuento   = null;
    public ?int    $activo      = 1;
    public ?string $created_at  = null;
    public ?string $updated_at  = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    public function getImageUrl(): string
    {
        if (!empty($this->imagen_url)) {
            return APP_URL . 'Content/Demo/img/Combos/' . $this->imagen_url;
        }
        return APP_URL . 'Content/Demo/img/default/producto.svg';
    }

    public function tieneDescuento(): bool
    {
        return $this->descuento !== null && (float)$this->descuento > 0;
    }

    // Calcula el precio total del combo dado el array de productos
    // Si tiene descuento aplica el porcentaje sobre la suma
    public function calcularPrecio(array $productos): float
    {
        $suma = 0;
        foreach ($productos as $p) {
            $suma += (float)$p['precio_unitario'] * (int)$p['cantidad'];
        }

        if ($this->tieneDescuento()) {
            $suma = $suma * (1 - (float)$this->descuento / 100);
        }

        return round($suma, 2);
    }

    // Calcula el ahorro en lempiras
    public function calcularAhorro(array $productos): float
    {
        $suma = 0;
        foreach ($productos as $p) {
            $suma += (float)$p['precio_unitario'] * (int)$p['cantidad'];
        }

        if (!$this->tieneDescuento()) return 0;

        return round($suma * (float)$this->descuento / 100, 2);
    }

    // Retorna el precio formateado
    public function getPrecioFormateado(array $productos): string
    {
        return 'L. ' . number_format($this->calcularPrecio($productos), 2);
    }

    // Retorna el descuento formateado
    public function getDescuentoFormateado(): string
    {
        if (!$this->tieneDescuento()) return '—';
        return number_format((float)$this->descuento, 0) . '%';
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre del combo es obligatorio.');
        }

        if ($this->descuento !== null && ((float)$this->descuento < 0 || (float)$this->descuento > 100)) {
            $this->addError('El descuento debe ser entre 0 y 100.');
        }

        return !$this->hasErrors();
    }
}