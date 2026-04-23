<?php

class CitaModel extends BaseModel
{
    protected string $table      = 'citas';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_citas_findAll');
        return array_map(fn($row) => CitaEntity::fromArray($row), $rows);
    }

    public function findById(int $id): CitaEntity
    {
        $row = $this->callSPSingle('sp_citas_findById', [$id]);
        if (!$row) return new CitaEntity();
        return CitaEntity::fromArray($row);
    }

    public function findByFecha(string $fecha): array
    {
        $rows = $this->callSP('sp_citas_findByFecha', [$fecha]);
        return array_map(fn($row) => CitaEntity::fromArray($row), $rows);
    }

    public function findByMes(int $anio, int $mes): array
    {
        return $this->callSP('sp_citas_findByMes', [$anio, $mes]);
    }

    public function getConfig(): ?array
    {
        return $this->callSPSingle('sp_citas_getConfig');
    }

    public function updateConfig(array $data): bool
    {
        $affected = $this->callSPExecute('sp_citas_updateConfig', [
            $data['horario_inicio'],
            $data['horario_fin'],
            $data['dias_laborales'],
            $data['duracion_default'],
            $data['capacidad_simultanea'],
        ]);
        return $affected >= 0;
    }

    // Verifica disponibilidad — retorna cuántas citas hay en ese rango
    public function verificarDisponibilidad(string $fecha, string $hora, int $duracion, int $excludeId = 0): int
    {
        $row = $this->callSPSingle('sp_citas_verificarDisponibilidad', [
            $fecha, $hora, $duracion, $excludeId ?: null
        ]);
        return $row ? (int) $row['ocupadas'] : 0;
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_citas_insert', [
            $data['cliente_id']  ?? null,
            $data['servicio_id'],
            $data['user_id']     ?? null,
            $data['fecha'],
            $data['hora_inicio'],
            $data['duracion'],
            $data['precio'],
            $data['nota']        ?? null,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_citas_update', [
            $data['id'],
            $data['cliente_id']  ?? null,
            $data['servicio_id'],
            $data['user_id']     ?? null,
            $data['fecha'],
            $data['hora_inicio'],
            $data['duracion'],
            $data['precio'],
            $data['nota']        ?? null,
        ]);
        return $affected >= 0;
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $affected = $this->callSPExecute('sp_citas_updateEstado', [$id, $estado]);
        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_citas_delete', [$id]);
        return $affected >= 0;
    }

    public function countHoy(): int
    {
        $row = $this->callSPSingle('sp_citas_countHoy');
        return $row ? (int) $row['total'] : 0;
    }

    public function countPendientes(): int
    {
        $row = $this->callSPSingle('sp_citas_countPendientes');
        return $row ? (int) $row['total'] : 0;
    }
    
    public function findByCliente(int $clienteId): array
    {
        $rows = $this->callSP('sp_citas_findByCliente', [$clienteId]);
        return array_map(fn($row) => CitaEntity::fromArray($row), $rows);
    }
}