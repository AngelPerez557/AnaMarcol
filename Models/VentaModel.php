<?php

class VentaModel extends BaseModel
{
    protected string $table      = 'ventas';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        return $this->callSP('sp_ventas_findAll');
    }

    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_ventas_findById', [$id]);
    }

    public function findDetalle(int $ventaId): array
    {
        return $this->callSP('sp_ventas_findDetalle', [$ventaId]);
    }

    public function getFacturacionConfig(): ?array
    {
        return $this->callSPSingle('sp_facturacion_getConfig');
    }

    public function countHoy(): int
    {
        $row = $this->callSPSingle('sp_ventas_countHoy');
        return $row ? (int) $row['total'] : 0;
    }

    public function totalHoy(): float
    {
        $row = $this->callSPSingle('sp_ventas_totalHoy');
        return $row ? (float) $row['total'] : 0.0;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_ventas_insert', [
            $data['cliente_id']     ?? null,
            $data['user_id'],
            $data['metodo_pago'],
            $data['subtotal'],
            $data['descuento']      ?? 0,
            $data['total'],
            $data['monto_recibido'] ?? null,
            $data['cambio']         ?? null,
            $data['nota']           ?? null,
        ]);
    }

    public function insertDetalle(array $data): bool
    {
        $affected = $this->callSPExecute('sp_ventas_insertDetalle', [
            $data['venta_id'],
            $data['producto_id'],
            $data['variante_id']    ?? null,
            $data['nombre_producto'],
            $data['precio_unit'],
            $data['cantidad'],
            $data['subtotal'],
        ]);
        return $affected >= 0;
    }
    
    public function updateFacturacionConfig(array $data): bool
    {
        $affected = $this->callSPExecute('sp_facturacion_updateConfig', [
            $data['rtn'],
            $data['cai'],
            $data['rango_desde'],
            $data['rango_hasta'],
            $data['fecha_limite'],
            $data['establecimiento'],
            $data['punto_emision'],
            $data['nombre_fiscal'],
            $data['direccion_fiscal'],
            $data['correlativo'],
        ]);
        return $affected >= 0;
    }
    // ─────────────────────────────────────────────
    // TRANSACCIONES PÚBLICAS
    // El controlador las llama directamente
    // ─────────────────────────────────────────────

    public function beginTransactionPublic(): void
    {
        $this->beginTransaction();
    }

    public function commitPublic(): void
    {
        $this->commit();
    }

    public function rollbackPublic(): void
    {
        $this->rollback();
    }
}