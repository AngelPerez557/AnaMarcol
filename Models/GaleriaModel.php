<?php

class GaleriaModel extends BaseModel
{
    protected string $table      = 'galeria_clientes';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        return $this->callSP('sp_galeria_findAll');
    }

    public function findActivas(): array
    {
        return $this->callSP('sp_galeria_findActivas');
    }

    public function findById(int $id): ?array
    {
        return $this->callSPSingle('sp_galeria_findById', [$id]);
    }

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_galeria_insert', [
            $data['imagen_url'],
            $data['descripcion'] ?? null,
            $data['orden']       ?? 0,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_galeria_update', [
            $data['id'],
            $data['imagen_url']  ?? null,
            $data['descripcion'] ?? null,
            $data['orden']       ?? 0,
        ]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        return $this->callSPExecute('sp_galeria_toggleActivo', [$id, $activo]) >= 0;
    }

    public function delete(int $id): bool
    {
        return $this->callSPExecute('sp_galeria_delete', [$id]) > 0;
    }
}