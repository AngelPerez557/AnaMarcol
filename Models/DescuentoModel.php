<?php

class DescuentoModel extends BaseModel
{
    protected string $table      = 'descuentos';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        return $this->callSP('sp_descuentos_findAll');
    }

    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_descuentos_findById', [$id]);
    }

    public function getActivo(): ?array
    {
        return $this->callSPSingle('sp_descuentos_getActivo');
    }

    // ─────────────────────────────────────────────
    // PRECIO FINAL — regla de negocio del descuento
    // Vive aquí y no en las Views: el carrito, la cotización y
    // el catálogo deben calcular exactamente el mismo precio.
    // $descuento se puede inyectar para no consultar la BD en un bucle.
    // ─────────────────────────────────────────────
    public function precioConDescuento(float $precioBase, int $categoriaId, ?array $descuento = null): float
    {
        $d = $descuento ?? $this->getActivo();
        if (empty($d)) return round($precioBase, 2);

        $aplica = ($d['aplica_a'] ?? '') === 'todo'
               || (($d['aplica_a'] ?? '') === 'categoria' && (int) ($d['categoria_id'] ?? 0) === $categoriaId);

        if (!$aplica) return round($precioBase, 2);

        return round($precioBase * (1 - (float) $d['porcentaje'] / 100), 2);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_descuentos_insert', [
            $data['nombre'],
            $data['porcentaje'],
            $data['aplica_a'],
            $data['categoria_id'] ?? null,
            $data['fecha_inicio'],
            $data['fecha_fin'],
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_descuentos_update', [
            $data['id'],
            $data['nombre'],
            $data['porcentaje'],
            $data['aplica_a'],
            $data['categoria_id'] ?? null,
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['activo'] ?? 1,
        ]);
        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_descuentos_delete', [$id]);
        return $affected > 0;
    }
}