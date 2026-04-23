# Manual del Sistema — AnaMarcolMakeupStudios
**DeskCod — PHP MVC Puro**

---

## 1. Arquitectura del Sistema

```
AnaMarcol/
├── Config/
│   ├── Define.php        → Constantes globales y helpers
│   ├── AutoLoad.php      → Cargador automático de clases
│   ├── JRequest.php      → Parsea la URL
│   ├── JRouter.php       → Enruta al controlador correcto
│   ├── Conexion.php      → Singleton PDO con MySQL
│   └── Core/
│       └── Auth.php      → Sesiones, permisos RBAC
├── Controllers/          → Lógica de cada módulo
├── Models/               → Acceso a la BD (via Stored Procedures)
├── Entity/               → Representación de filas de BD como objetos
├── Views/                → HTML de cada módulo
├── Template/             → Layout base (header, menu, footer)
├── Content/              → CSS, JS, imágenes
├── BD/                   → Scripts SQL
├── index.php             → Front Controller (punto de entrada único)
└── .htaccess             → Reescritura de URLs
```

---

## 2. Flujo de una Petición

```
Usuario → URL → .htaccess → index.php → JRequest → JRouter
→ Controller → Model → Entity → View → Respuesta HTML
```

**Ejemplo: `/Productos/index`**

1. `.htaccess` convierte la URL a `index.php?url=Productos/index`
2. `index.php` verifica sesión y carga el Template
3. `JRequest` extrae `Controller=Productos`, `Method=index`
4. `JRouter` instancia `ProductosController` y llama `index()`
5. `ProductosController::index()` llama a `ProductoModel::findAll()`
6. `ProductoModel` ejecuta `CALL sp_productos_findAll()` en MySQL
7. El resultado se mapea a un array de `ProductoEntity`
8. El controlador hace `require_once VIEWS_PATH . 'Productos/index.php'`
9. La vista usa las variables del controlador para renderizar el HTML

---

## 3. Cómo Crear un Módulo Completo

Ejemplo: módulo **Proveedores**

### Paso 1 — SQL: Tabla y Stored Procedures

```sql
-- Tabla
CREATE TABLE IF NOT EXISTS proveedores (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(150)  NOT NULL,
    telefono    VARCHAR(20)   DEFAULT NULL,
    email       VARCHAR(120)  DEFAULT NULL,
    direccion   TEXT          DEFAULT NULL,
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SPs mínimos
DELIMITER $$
CREATE PROCEDURE sp_proveedores_findAll()
BEGIN
    SELECT id, nombre, telefono, email, direccion, activo, created_at
    FROM proveedores ORDER BY nombre ASC;
END$$

CREATE PROCEDURE sp_proveedores_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, telefono, email, direccion, activo, created_at
    FROM proveedores WHERE id=p_id LIMIT 1;
END$$

CREATE PROCEDURE sp_proveedores_insert(
    IN p_nombre VARCHAR(150), IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(120), IN p_direccion TEXT
)
BEGIN
    INSERT INTO proveedores (nombre, telefono, email, direccion)
    VALUES (p_nombre, p_telefono, p_email, p_direccion);
    SELECT LAST_INSERT_ID() AS id;
END$$

CREATE PROCEDURE sp_proveedores_update(
    IN p_id INT, IN p_nombre VARCHAR(150), IN p_telefono VARCHAR(20),
    IN p_email VARCHAR(120), IN p_direccion TEXT
)
BEGIN
    UPDATE proveedores SET nombre=p_nombre, telefono=p_telefono,
        email=p_email, direccion=p_direccion WHERE id=p_id;
END$$

CREATE PROCEDURE sp_proveedores_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE proveedores SET activo=p_activo WHERE id=p_id;
END$$

CREATE PROCEDURE sp_proveedores_delete(IN p_id INT)
BEGIN
    UPDATE proveedores SET activo=0 WHERE id=p_id;
END$$

CREATE PROCEDURE sp_proveedores_count()
BEGIN
    SELECT COUNT(*) AS total FROM proveedores;
END$$
DELIMITER ;
```

### Paso 2 — Entity: `Entity/ProveedorEntity.php`

```php
<?php
class ProveedorEntity extends BaseEntity
{
    // Propiedades con nombres IDÉNTICOS a las columnas de la BD
    public ?int    $id         = null;
    public ?string $nombre     = null;
    public ?string $telefono   = null;
    public ?string $email      = null;
    public ?string $direccion  = null;
    public ?int    $activo     = 1;
    public ?string $created_at = null;

    // Helper — retorna true si está activo
    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    // Validación — se llama antes de guardar
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre es obligatorio.');
        }

        return !$this->hasErrors();
    }
}
```

**Reglas de Entity:**
- Una propiedad por columna de la BD, con el mismo nombre exacto
- `isValid()` siempre requerido (abstracto en BaseEntity)
- Agregar helpers para lógica de presentación (`isActivo()`, `getNombreFormateado()`, etc.)
- Nunca poner lógica de BD en la Entity

### Paso 3 — Model: `Models/ProveedorModel.php`

```php
<?php
class ProveedorModel extends BaseModel
{
    // OBLIGATORIO: nombre de la tabla en la BD
    protected string $table      = 'proveedores';
    protected string $primaryKey = 'id';

    // findAll — retorna array de ProveedorEntity
    public function findAll(): array
    {
        // callSP() → ejecuta CALL sp_proveedores_findAll()
        // array_map → convierte cada fila en ProveedorEntity
        $rows = $this->callSP('sp_proveedores_findAll');
        return array_map(fn($row) => ProveedorEntity::fromArray($row), $rows);
    }

    // findById — retorna una sola ProveedorEntity
    public function findById(int $id): ProveedorEntity
    {
        // callSPSingle() → retorna una sola fila o null
        $row = $this->callSPSingle('sp_proveedores_findById', [$id]);

        // Si no existe retorna entidad vacía con Found=false
        if ($row === null) return new ProveedorEntity();

        return ProveedorEntity::fromArray($row);
    }

    // insert — retorna el ID del registro creado
    public function insert(array $data): int
    {
        // callSPInsert() → ejecuta SP y retorna LAST_INSERT_ID()
        return $this->callSPInsert('sp_proveedores_insert', [
            $data['nombre'],
            $data['telefono'] ?? null,
            $data['email']    ?? null,
            $data['direccion']?? null,
        ]);
        // IMPORTANTE: el orden de los parámetros debe coincidir
        // EXACTAMENTE con el orden de los IN del SP
    }

    // update — retorna true si se actualizó
    public function update(array $data): bool
    {
        // callSPExecute() → ejecuta SP sin retorno, devuelve filas afectadas
        $affected = $this->callSPExecute('sp_proveedores_update', [
            $data['id'],
            $data['nombre'],
            $data['telefono'] ?? null,
            $data['email']    ?? null,
            $data['direccion']?? null,
        ]);
        return $affected > 0;
    }

    public function toggleActivo(int $id, int $activo): bool
    {
        $affected = $this->callSPExecute('sp_proveedores_toggleActivo', [$id, $activo]);
        return $affected > 0;
    }

    public function delete(int $id): bool
    {
        $affected = $this->callSPExecute('sp_proveedores_delete', [$id]);
        return $affected > 0;
    }

    public function count(): int
    {
        $row = $this->callSPSingle('sp_proveedores_count');
        return $row ? (int) $row['total'] : 0;
    }
}
```

**Métodos de BaseModel disponibles:**
| Método | Uso |
|--------|-----|
| `callSP($sp, $params)` | Retorna array de filas (findAll) |
| `callSPSingle($sp, $params)` | Retorna una fila o null (findById) |
| `callSPExecute($sp, $params)` | Retorna filas afectadas (update/delete) |
| `callSPInsert($sp, $params)` | Retorna LAST_INSERT_ID (insert) |
| `beginTransaction()` | Inicia transacción |
| `commit()` | Confirma transacción |
| `rollback()` | Revierte transacción |

### Paso 4 — Controller: `Controllers/ProveedoresController.php`

```php
<?php
class ProveedoresController
{
    private ProveedorModel $model;

    public function __construct()
    {
        Auth::check();                   // Verifica sesión activa
        $this->model = new ProveedorModel();
    }

    // INDEX — Listado
    // URL: /Proveedores/index
    public function index(): void
    {
        Auth::require('proveedores.ver'); // Verifica permiso

        $pageTitle   = 'Proveedores';
        $proveedores = $this->model->findAll();

        // SIEMPRE al final del método — carga la vista
        require_once VIEWS_PATH . 'Proveedores' . DS . 'index.php';
    }

    // REGISTRY — Crear o editar
    // URL: /Proveedores/registry        → crear
    // URL: /Proveedores/registry/5      → editar ID 5
    public function registry(string $id = ''): void
    {
        $esEdicion = !empty($id) && is_numeric($id);
        Auth::require($esEdicion ? 'proveedores.editar' : 'proveedores.crear');

        $pageTitle  = $esEdicion ? 'Editar Proveedor' : 'Nuevo Proveedor';
        $proveedor  = $esEdicion
            ? $this->model->findById((int) $id)
            : new ProveedorEntity();

        // Si viene un ID pero no existe → redirigir
        if ($esEdicion && !$proveedor->Found) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error','text'=>'No existe.'];
            header('Location: ' . APP_URL . 'Proveedores/index');
            exit();
        }

        require_once VIEWS_PATH . 'Proveedores' . DS . 'registry.php';
    }

    // SAVE — Guardar (POST)
    // URL: /Proveedores/save
    public function save(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . 'Proveedores/index');
            exit();
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $esEdicion = $id > 0;

        Auth::require($esEdicion ? 'proveedores.editar' : 'proveedores.crear');

        // 1. Validar CSRF
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            $_SESSION['alert'] = ['icon'=>'error','title'=>'Error de seguridad','text'=>'Token inválido.'];
            header('Location: ' . APP_URL . 'Proveedores/index');
            exit();
        }

        // 2. Sanitizar entradas
        $nombre    = htmlspecialchars(strip_tags(trim($_POST['nombre']    ?? '')));
        $telefono  = htmlspecialchars(strip_tags(trim($_POST['telefono']  ?? '')));
        $email     = htmlspecialchars(strip_tags(trim($_POST['email']     ?? '')));
        $direccion = htmlspecialchars(strip_tags(trim($_POST['direccion'] ?? '')));

        // 3. Validar campos obligatorios
        if (empty($nombre)) {
            $_SESSION['alert'] = ['icon'=>'warning','title'=>'Requerido','text'=>'El nombre es obligatorio.'];
            $redirect = $esEdicion
                ? APP_URL . 'Proveedores/registry/' . $id
                : APP_URL . 'Proveedores/registry';
            header('Location: ' . $redirect);
            exit();
        }

        // 4. Preparar datos
        $data = [
            'nombre'    => $nombre,
            'telefono'  => $telefono ?: null,
            'email'     => $email    ?: null,
            'direccion' => $direccion?: null,
        ];

        // 5. Guardar
        if ($esEdicion) {
            $data['id'] = $id;
            $ok         = $this->model->update($data);
            $mensaje    = $ok ? 'Proveedor actualizado.' : 'Error al actualizar.';
        } else {
            $nuevoId = $this->model->insert($data);
            $ok      = $nuevoId > 0;
            $mensaje = $ok ? 'Proveedor creado.' : 'Error al crear.';
        }

        // 6. Alert de resultado
        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Éxito'   : 'Error',
            'text'  => $mensaje,
        ];

        header('Location: ' . APP_URL . 'Proveedores/index');
        exit();
    }

    // TOGGLE — Activar/Desactivar (POST — responde JSON)
    public function toggle(): void
    {
        Auth::require('proveedores.editar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403); exit();
        }

        $id     = (int) ($_POST['id']     ?? 0);
        $activo = (int) ($_POST['activo'] ?? 0);
        $ok     = $this->model->toggleActivo($id, $activo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit();
    }

    // DELETE — Eliminar (POST)
    public function delete(): void
    {
        Auth::require('proveedores.eliminar');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit(); }
        if (!isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
            http_response_code(403); exit();
        }

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $this->model->delete($id);

        $_SESSION['alert'] = [
            'icon'  => $ok ? 'success' : 'error',
            'title' => $ok ? 'Eliminado' : 'Error',
            'text'  => $ok ? 'Proveedor eliminado.' : 'Error al eliminar.',
        ];

        header('Location: ' . APP_URL . 'Proveedores/index');
        exit();
    }
}
```

**Reglas del Controlador:**
- `Auth::check()` siempre en el constructor
- `Auth::require('modulo.accion')` al inicio de cada método
- Validar CSRF en todos los POST
- Sanitizar todas las entradas con `htmlspecialchars(strip_tags(trim()))`
- `require_once VIEWS_PATH` siempre al FINAL del método
- Nunca hacer queries directamente — siempre via Model
- Guardar alertas en `$_SESSION['alert']` y redirigir con `header()` + `exit()`

### Paso 5 — Vistas

#### `Views/Proveedores/index.php`

```php
<div class="container-fluid py-4">

    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-industry me-2" style="color:#de777d;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($proveedores) ?> registrados</small>
        </div>
        <?php if (Auth::can('proveedores.crear')): ?>
        <a href="<?= APP_URL ?>Proveedores/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Proveedor
        </a>
        <?php endif; ?>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:rgba(222,119,125,0.08);">
                        <th class="ps-4">#</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proveedores)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            No hay proveedores registrados.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($proveedores as $i => $p): ?>
                    <tr>
                        <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($p->nombre) ?></td>
                        <td><?= htmlspecialchars($p->telefono ?? '—') ?></td>
                        <td><?= htmlspecialchars($p->email    ?? '—') ?></td>
                        <td class="text-center">
                            <?php if (Auth::can('proveedores.editar')): ?>
                            <div class="form-check form-switch d-inline-block mb-0">
                                <input class="form-check-input toggle-activo"
                                       type="checkbox" role="switch"
                                       id="toggle-<?= $p->id ?>"
                                       data-id="<?= $p->id ?>"
                                       data-url="<?= APP_URL ?>Proveedores/toggle"
                                       data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                       <?= $p->isActivo() ? 'checked' : '' ?>>
                                <label class="form-check-label" for="toggle-<?= $p->id ?>"></label>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <?php if (Auth::can('proveedores.editar')): ?>
                                <a href="<?= APP_URL ?>Proveedores/registry/<?= $p->id ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (Auth::can('proveedores.eliminar')): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete"
                                        data-id="<?= $p->id ?>"
                                        data-nombre="<?= htmlspecialchars($p->nombre) ?>"
                                        data-url="<?= APP_URL ?>Proveedores/delete"
                                        data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle activo
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const id = this.dataset.id, url = this.dataset.url,
                  csrf = this.dataset.csrf, activo = this.checked ? 1 : 0, self = this;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.mixin({ toast:true, position:'top-end',
                        showConfirmButton:false, timer:2000 })
                    .fire({ icon:'success', title: activo ? 'Activado' : 'Desactivado' });
                } else {
                    self.checked = !self.checked;
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // Eliminar
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id, nombre = this.dataset.nombre,
                  url = this.dataset.url, csrf = this.dataset.csrf;

            Swal.fire({
                icon:'warning', title:'¿Eliminar?', text:`"${nombre}" será desactivado.`,
                showCancelButton:true, confirmButtonColor:'#dc3545',
                confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST'; form.action = url;
                    form.innerHTML = `<input type="hidden" name="id" value="${id}">
                                      <input type="hidden" name="csrf_token" value="${csrf}">`;
                    document.body.appendChild(form); form.submit();
                }
            });
        });
    });
});
</script>
```

#### `Views/Proveedores/registry.php`

```php
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold"><?= htmlspecialchars($pageTitle) ?></h4>
        <a href="<?= APP_URL ?>Proveedores/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header">Datos del proveedor</div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Proveedores/save" autocomplete="off">

                        <!-- CSRF — SIEMPRE en todos los formularios -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- ID oculto solo en edición -->
                        <?php if ($proveedor->Found): ?>
                        <input type="hidden" name="id" value="<?= $proveedor->id ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   value="<?= htmlspecialchars($proveedor->nombre ?? '') ?>"
                                   maxlength="150" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label fw-semibold">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono"
                                   value="<?= htmlspecialchars($proveedor->telefono ?? '') ?>"
                                   maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($proveedor->email ?? '') ?>"
                                   maxlength="120">
                        </div>

                        <div class="mb-4">
                            <label for="direccion" class="form-label fw-semibold">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion"
                                      rows="2"><?= htmlspecialchars($proveedor->direccion ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $proveedor->Found ? 'Guardar cambios' : 'Crear proveedor' ?>
                            </button>
                            <a href="<?= APP_URL ?>Proveedores/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 4. Seguridad — Checklist por Módulo

Antes de dar por terminado cualquier módulo verificar:

| Punto | Descripción |
|-------|-------------|
| ✅ `Auth::check()` | En el constructor de cada controlador |
| ✅ `Auth::require()` | Al inicio de cada método |
| ✅ CSRF token | En cada formulario HTML y validado en cada POST |
| ✅ Sanitización | `htmlspecialchars(strip_tags(trim()))` en cada entrada |
| ✅ Tipos | `(int)`, `(float)`, `?: null` en cada variable |
| ✅ `header()` + `exit()` | Siempre juntos en redirecciones |
| ✅ `$_SESSION['alert']` | Para mensajes entre redirecciones |
| ✅ Soft delete | `activo=0` en lugar de DELETE real |
| ✅ `require_once VIEWS_PATH` | Al final del método, nunca al inicio |

---

## 5. Agregar al Menú

En `Template/Default/menu.php`, dentro del array `$menu`:

```php
'Proveedores' => [
    'Id'      => 15,           // ID único en el menú
    'Nombre'  => 'Proveedores',
    'Url'     => APP_URL . 'Proveedores/index',
    'Icono'   => 'fas fa-industry',
    'Permiso' => 'proveedores.ver',  // Permiso requerido para ver el ítem
],
```

---

## 6. Agregar Permisos en la BD

```sql
INSERT INTO permissions (nombre, slug, modulo) VALUES
    ('Ver proveedores',     'proveedores.ver',     'proveedores'),
    ('Crear proveedores',   'proveedores.crear',   'proveedores'),
    ('Editar proveedores',  'proveedores.editar',  'proveedores'),
    ('Eliminar proveedores','proveedores.eliminar','proveedores');

-- Asignar al admin
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permissions WHERE slug LIKE 'proveedores.%';
```

---

## 7. Convenciones de Nomenclatura

| Elemento | Convención | Ejemplo |
|----------|-----------|---------|
| Tabla BD | snake_case plural | `producto_variantes` |
| SP | `sp_tabla_accion` | `sp_proveedores_findAll` |
| Entity | PascalCase + Entity | `ProveedorEntity` |
| Model | PascalCase + Model | `ProveedorModel` |
| Controller | PascalCase + Controller | `ProveedoresController` |
| Vista index | `Views/Modulo/index.php` | `Views/Proveedores/index.php` |
| Vista form | `Views/Modulo/registry.php` | `Views/Proveedores/registry.php` |
| URL | `/Controlador/metodo` | `/Proveedores/index` |
| Permiso | `modulo.accion` | `proveedores.crear` |

---

## 8. Paleta de Colores

```css
--marcol-pink:       #de777d   /* Color primario — botones, badges, acentos */
--marcol-pink-hover: #c56d71   /* Hover del primario */
--marcol-pink-dark:  #b05a60   /* Active / pressed */
--marcol-pink-soft:  #f5e6e7   /* Fondos suaves */
```

---

*Sistema desarrollado por DeskCod — AnaMarcolMakeupStudios v1.0.0*