<?php

class UserModel extends BaseModel
{
    protected string $table      = 'users';
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    public function findAll(): array
    {
        $rows = $this->callSP('sp_users_findAll');
        return array_map(fn($row) => UserEntity::fromArray($row), $rows);
    }

    public function findById(int $id): UserEntity
    {
        $row = $this->callSPSingle('sp_users_findById', [$id]);
        if (!$row) return new UserEntity();
        return UserEntity::fromArray($row);
    }

    public function findByEmail(string $email): UserEntity
    {
        $row = $this->callSPSingle('sp_users_findByEmail', [$email]);
        if (!$row) return new UserEntity();
        return UserEntity::fromArray($row);
    }

    public function findByRol(int $rolId): array
    {
        $rows = $this->callSP('sp_users_findByRol', [$rolId]);
        return array_map(fn($row) => UserEntity::fromArray($row), $rows);
    }

    public function findByUsername(string $username): UserEntity
    {
        $row = $this->callSPSingle('sp_users_findByUsername', [$username]);
        if (!$row) return new UserEntity();
        return UserEntity::fromArray($row);
    }

    public function findByEmailOrUsername(string $credencial): UserEntity
    {
        // Intentar por email primero
        $user = $this->findByEmail($credencial);
        if ($user->Found) return $user;

        // Si no encontró por email intentar por username
        return $this->findByUsername($credencial);
    }

    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $row = $this->callSPSingle('sp_users_usernameExists', [$username, $excludeId]);
        return $row ? (int) $row['existe'] > 0 : false;
    }

    public function count(): int
    {
        $row = $this->callSPSingle('sp_users_count');
        return $row ? (int) $row['total'] : 0;
    }

    public function countActivos(): int
    {
        $row = $this->callSPSingle('sp_users_countActivos');
        return $row ? (int) $row['total'] : 0;
    }

    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $row = $this->callSPSingle('sp_users_emailExists', [$email, $excludeId]);
        return $row ? (int) $row['existe'] > 0 : false;
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    public function insert(array $data): int
    {
        return $this->callSPInsert('sp_users_insert', [
            $data['nombre'],
            $data['username'] ?? null,
            $data['email'],
            $data['password'],
            $data['rol_id'],
            $data['activo']   ?? 1,
            $data['foto']     ?? null,
            $data['telefono'] ?? null,
        ]);
    }

    public function update(array $data): bool
    {
        $affected = $this->callSPExecute('sp_users_update', [
            $data['id'],
            $data['nombre'],
            $data['username'] ?? null,
            $data['email'],
            $data['rol_id'],
            $data['activo']   ?? 1,
            $data['foto']     ?? null,
            $data['telefono'] ?? null,
        ]);
        return $affected >= 0;
    }

    public function updatePassword(int $id, string $password): bool
    {
        $affected = $this->callSPExecute('sp_users_updatePassword', [$id, $password]);
        return $affected >= 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_users_toggleActivo', [$id, $activo]);
        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_users_delete', [$id]);
        return $affected > 0;
    }

    public function marcarTour(int $id): void
    {
        $this->callSPExecute('sp_users_marcarTour', [$id]);
    }

    public function activarTour(int $id): void
    {
        $this->callSPExecute('sp_users_activarTour', [$id]);
    }
    public function updatePerfil(array $data): bool
    {
        $affected = $this->callSPExecute('sp_users_updatePerfil', [
            $data['id'],
            $data['nombre'],
            $data['username'] ?? null,
            $data['email'],
            $data['telefono'] ?? null,
            $data['foto']     ?? null,
        ]);
        return $affected >= 0;
    }
}