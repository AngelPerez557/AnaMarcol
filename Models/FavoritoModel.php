<?php

class FavoritoModel extends BaseModel
{
    protected string $table      = 'favoritos';
    protected string $primaryKey = 'id';

    public function toggle(int $clienteId, int $productoId): int
    {
        $row = $this->callSPSingle('sp_favoritos_toggle', [$clienteId, $productoId]);
        return $row ? (int)$row['liked'] : 0;
    }

    public function isFavorito(int $clienteId, int $productoId): bool
    {
        $row = $this->callSPSingle('sp_favoritos_isFavorito', [$clienteId, $productoId]);
        return $row && (int)$row['es_favorito'] > 0;
    }

    public function findByCliente(int $clienteId): array
    {
        return $this->callSP('sp_favoritos_findByCliente', [$clienteId]);
    }
}