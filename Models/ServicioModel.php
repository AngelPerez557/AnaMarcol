<?php

class ServicioModel extends BaseModel
{
    protected string $table      = 'servicios_cita';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_servicios_findAll');
        return array_map(fn($row) => ServicioEntity::fromArray($row), $rows);
    }

    public function findActivos(): array
    {
        $rows = $this->callSP('sp_servicios_findActivos');
        return array_map(fn($row) => ServicioEntity::fromArray($row), $rows);
    }

    public function findById(int $id): ServicioEntity
    {
        $row = $this->callSPSingle('sp_servicios_findById', [$id]);
        if (!$row) return new ServicioEntity();
        return ServicioEntity::fromArray($row);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_servicios_insert', [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['precio_base'],
            $data['duracion'],
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_servicios_update', [
            $data['id'],
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['precio_base'],
            $data['duracion'],
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_servicios_toggleActivo', [$id, $activo]);
        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_servicios_delete', [$id]);
        return $affected >= 0;
    }
}