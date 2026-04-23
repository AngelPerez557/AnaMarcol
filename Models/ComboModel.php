<?php

class ComboModel extends BaseModel
{
    protected string $table      = 'combos';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        $rows = $this->callSP('sp_combos_findAll');
        return array_map(fn($row) => ComboEntity::fromArray($row), $rows);
    }

    public function findActivos(): array
    {
        $rows = $this->callSP('sp_combos_findActivos');
        return array_map(fn($row) => ComboEntity::fromArray($row), $rows);
    }

    public function findById(int $id): ComboEntity
    {
        $row = $this->callSPSingle('sp_combos_findById', [$id]);
        if (!$row) return new ComboEntity();
        return ComboEntity::fromArray($row);
    }

    // Retorna los productos de un combo con toda su info
    public function findProductos(int $comboId): array
    {
        return $this->callSP('sp_combos_findProductos', [$comboId]);
    }

    public function count(): int
    {
        $row = $this->callSPSingle('sp_combos_count');
        return $row ? (int) $row['total'] : 0;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_combos_insert', [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['imagen_url']  ?? null,
            $data['descuento']   ?? null,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_combos_update', [
            $data['id'],
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['imagen_url']  ?? null,
            $data['descuento']   ?? null,
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_combos_toggleActivo', [$id, $activo]);
        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_combos_delete', [$id]);
        return $affected >= 0;
    }

    // ─────────────────────────────────────────────
    // PRODUCTOS DEL COMBO
    // ─────────────────────────────────────────────

    public function addProducto(int $comboId, int $productoId, ?int $varianteId, int $cantidad): bool
    {
        $affected = $this->callSPExecute('sp_combos_addProducto', [
            $comboId,
            $productoId,
            $varianteId,
            $cantidad,
        ]);
        return $affected >= 0;
    }

    public function removeProducto(int $id): bool
    {
        $affected = $this->callSPExecute('sp_combos_removeProducto', [$id]);
        return $affected > 0;
    }

    // Sincroniza productos del combo — borra los actuales y agrega los nuevos
    public function syncProductos(int $comboId, array $productos): bool
    {
        $this->beginTransaction();
        try {
            $this->callSPExecute('sp_combos_clearProductos', [$comboId]);

            foreach ($productos as $p) {
                $this->callSPExecute('sp_combos_addProducto', [
                    $comboId,
                    (int) $p['producto_id'],
                    !empty($p['variante_id']) ? (int) $p['variante_id'] : null,
                    (int) ($p['cantidad'] ?? 1),
                ]);
            }

            $this->commit();
            return true;
        } catch (\RuntimeException $e) {
            $this->rollback();
            return false;
        }
    }
}