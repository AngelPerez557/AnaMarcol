<?php

/**
 * CotizacionModel — Persistencia de las cotizaciones de la tienda.
 *
 * Toda consulta pasa por SPs (sp_cotizaciones_*). El Model NO
 * descuenta stock ni crea ventas: una cotización no mueve inventario.
 */
class CotizacionModel extends BaseModel
{
    protected string $table      = 'cotizaciones';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        $rows = $this->callSP('sp_cotizaciones_findAll');
        return array_map(fn($row) => CotizacionEntity::fromArray($row), $rows);
    }

    public function findByEstado(string $estado): array
    {
        $rows = $this->callSP('sp_cotizaciones_findByEstado', [$estado]);
        return array_map(fn($row) => CotizacionEntity::fromArray($row), $rows);
    }

    public function findById(int $id): CotizacionEntity
    {
        $row = $this->callSPSingle('sp_cotizaciones_findById', [$id]);
        if (!$row) return new CotizacionEntity();
        return CotizacionEntity::fromArray($row);
    }

    public function findDetalle(int $cotizacionId): array
    {
        return $this->callSP('sp_cotizaciones_findDetalle', [$cotizacionId]);
    }

    public function countByEstado(string $estado): int
    {
        $row = $this->callSPSingle('sp_cotizaciones_countByEstado', [$estado]);
        return $row ? (int) $row['total'] : 0;
    }

    public function countHoy(): int
    {
        $row = $this->callSPSingle('sp_cotizaciones_countHoy');
        return $row ? (int) $row['total'] : 0;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_cotizaciones_insert', [
            $data['codigo'],
            $data['cliente_id']      ?? null,
            $data['nombre_cliente'],
            $data['wa_numero'],
            $data['tipo_entrega']    ?? 'Retiro',
            $data['direccion_envio'] ?? null,
            $data['zona_id']         ?? null,
            $data['subtotal']        ?? 0,
            $data['costo_envio']     ?? 0,
            $data['total']           ?? 0,
            $data['nota']            ?? null,
        ]);
    }

    public function insertDetalle(array $data): bool
    {
        $affected = $this->callSPExecute('sp_cotizaciones_insertDetalle', [
            $data['cotizacion_id'],
            $data['producto_id']     ?? null,
            $data['variante_id']     ?? null,
            $data['nombre_producto'],
            $data['variante_nombre'] ?? null,
            $data['precio_unit']     ?? 0,
            $data['cantidad']        ?? 1,
            $data['subtotal']        ?? 0,
        ]);
        return $affected >= 0;
    }

    public function updateEstado(int $id, string $estado): bool
    {
        if (!in_array($estado, CotizacionEntity::ESTADOS, true)) {
            return false;
        }
        $affected = $this->callSPExecute('sp_cotizaciones_updateEstado', [$id, $estado]);
        return $affected >= 0;
    }

    // Código único de 8 caracteres — mismo criterio que pedidos
    public function generarCodigo(): string
    {
        do {
            $codigo = strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
            $existe = $this->callSPSingle('sp_cotizaciones_existeCodigo', [$codigo]);
        } while ($existe && (int) $existe['total'] > 0);

        return $codigo;
    }
}
