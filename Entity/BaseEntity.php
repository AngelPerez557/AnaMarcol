<?php

/**
 * BaseEntity — Clase base de todas las entidades del sistema.
 *
 * Implementa ArrayAccess para que las entidades puedan accederse
 * indistintamente con sintaxis de objeto ($e->campo) o de array
 * ($e['campo']). Esto evita el error fatal:
 *   "Cannot use object of type XEntity as array"
 * cuando una View espera arrays pero el Model retorna entidades.
 *
 * Si una clave no existe como propiedad, offsetGet retorna null
 * (la View lo maneja con ?? '' / ?? 0), nunca lanza error.
 */
abstract class BaseEntity implements ArrayAccess
{
    // Indica si el registro fue encontrado en la BD
    // false por defecto — se pone true cuando el SP retorna datos
    public bool $Found = false;

    // ─────────────────────────────────────────────
    // ArrayAccess — acceso $entidad['campo']
    // ─────────────────────────────────────────────

    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, (string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $key = (string) $offset;
        return property_exists($this, $key) ? $this->$key : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset !== null && property_exists($this, (string) $offset)) {
            $this->{(string) $offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        // No-op: las entidades tienen propiedades fijas, no se eliminan.
    }

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // Recibe el array de PDO y mapea las propiedades
    // ─────────────────────────────────────────────
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
            $this->Found = true;
        }
    }

    // ─────────────────────────────────────────────
    // FACTORY METHOD
    // Crea una instancia de la entidad hija desde
    // un array asociativo retornado por PDO
    // Ej: UserEntity::fromArray($row)
    // ─────────────────────────────────────────────
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    // ─────────────────────────────────────────────
    // FILL — Mapeo directo de propiedades públicas
    // Asigna cada clave del array a la propiedad
    // correspondiente si existe en la entidad hija
    // ─────────────────────────────────────────────
    protected function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            // Solo asigna si la propiedad existe en la entidad hija
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // ─────────────────────────────────────────────
    // TO ARRAY
    // Convierte la entidad a array asociativo
    // Útil para pasar datos a los SPs o a la API
    // ─────────────────────────────────────────────
    public function toArray(): array
    {
        $data = [];
        // Obtiene todas las propiedades públicas de la entidad hija
        $reflect = new ReflectionClass($this);

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            // Excluye la propiedad Found — es de control interno
            if ($name === 'Found') continue;

            $data[$name] = $this->$name;
        }

        return $data;
    }

    // ─────────────────────────────────────────────
    // TO JSON
    // Serializa la entidad a JSON para la API
    // ─────────────────────────────────────────────
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // Cada entidad hija define sus propias reglas
    // ─────────────────────────────────────────────
    abstract public function isValid(): bool;

    // Array de errores de validación
    protected array $errors = [];

    // Retorna todos los errores de validación
    public function getErrors(): array
    {
        return $this->errors;
    }

    // Retorna el primer error de validación
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    // Verifica si hay errores de validación
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    // Agrega un error de validación
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    // Limpia todos los errores de validación
    protected function clearErrors(): void
    {
        $this->errors = [];
    }
}