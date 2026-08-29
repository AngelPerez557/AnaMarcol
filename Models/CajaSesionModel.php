<?php

class CajaSesionModel extends BaseModel
{
    protected string $table      = 'caja_sesiones';
    protected string $primaryKey = 'id';

    // Retorna la sesión abierta del usuario o null
    public function getSesionAbierta(int $userId): ?array
    {
        return $this->callSPSingle('sp_caja_getSesionAbierta', [$userId]);
    }

    // Abre una nueva caja — retorna -1 si ya tiene una abierta
    public function abrir(int $userId, float $montoApertura, string $nota = ''): int
    {
        $row = $this->callSPSingle('sp_caja_abrir', [
            $userId,
            $montoApertura,
            $nota ?: null,
        ]);
        return $row ? (int) $row['id'] : 0;
    }

    // Calcula los totales de la sesión desde la BD
    public function calcularTotales(int $sesionId, int $userId, string $abierataAt): ?array
    {
        return $this->callSPSingle('sp_caja_calcularTotales', [
            $sesionId,
            $userId,
            $abierataAt,
        ]);
    }

    // Cierra la caja
    public function cerrar(array $data): bool
    {
        $row = $this->callSPSingle('sp_caja_cerrar', [
            $data['sesion_id'],
            $data['user_id'],
            $data['monto_cierre'],
            $data['monto_sistema'],
            $data['total_ventas'],
            $data['total_efectivo'],
            $data['total_tarjeta'],
            $data['total_transferencia'],
            $data['total_anuladas'],
            $data['nota_cierre'] ?? null,
        ]);
        return $row && (int) $row['afectado'] > 0;
    }

    // Historial de todas las sesiones
    public function historial(): array
    {
        return $this->callSP('sp_caja_historial');
    }

    // Obtener sesión por ID
    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_caja_findById', [$id]);
    }

    // ─────────────────────────────────────────────
    // SYNC DESDE EL POS LOCAL
    // Idempotente por pos_sesion_id — reintentar el mismo envío no duplica.
    // Llega siempre ya cerrado: el POS solo sincroniza al cerrar caja.
    // ─────────────────────────────────────────────
    public function insertDesdePos(array $data): int
    {
        $row = $this->callSPSingle('sp_caja_insertDesdePos', [
            $data['pos_sesion_id'],
            $data['monto_apertura'],
            $data['monto_cierre'],
            $data['monto_sistema'],
            $data['diferencia'],
            $data['total_ventas'],
            $data['total_efectivo'],
            $data['total_tarjeta'],
            $data['total_transferencia'],
            $data['nota_apertura'] ?? null,
            $data['nota_cierre']   ?? null,
            $data['abierta_at'],
            $data['cerrada_at'],
        ]);
        return $row ? (int) $row['id'] : 0;
    }
}