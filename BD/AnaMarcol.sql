-- ============================================================
-- AnaMarcolMakeupStudios — BD Completa v1.0
-- Generado por DeskCod
-- ============================================================
-- INSTRUCCIONES:
-- 1. Abrir phpMyAdmin
-- 2. Crear BD vacía llamada "anamarcol"
-- 3. Seleccionar la BD y ejecutar este script completo
-- ============================================================

CREATE DATABASE IF NOT EXISTS anamarcol
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE anamarcol;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLAS
-- ============================================================

-- ── ROLES ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(60)   NOT NULL,
    slug        VARCHAR(60)   NOT NULL,
    descripcion VARCHAR(255)  DEFAULT NULL,
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PERMISOS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS permissions (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)  NOT NULL,
    slug        VARCHAR(100)  NOT NULL,
    modulo      VARCHAR(60)   NOT NULL,
    descripcion VARCHAR(255)  DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ROL PERMISOS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS rol_permisos (
    rol_id      INT UNSIGNED NOT NULL,
    permiso_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (rol_id, permiso_id),
    CONSTRAINT fk_rp_rol     FOREIGN KEY (rol_id)     REFERENCES roles       (id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_permiso FOREIGN KEY (permiso_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── USUARIOS PANEL ADMIN ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre           VARCHAR(120)  NOT NULL,
    username         VARCHAR(60)   DEFAULT NULL,
    email            VARCHAR(120)  NOT NULL,
    password         VARCHAR(255)  NOT NULL,
    rol_id           INT UNSIGNED  NOT NULL,
    activo           TINYINT(1)    NOT NULL DEFAULT 1,
    foto             VARCHAR(255)  DEFAULT NULL,
    telefono         VARCHAR(20)   DEFAULT NULL,
    tour_completado  TINYINT(1)    NOT NULL DEFAULT 0,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email    (email),
    UNIQUE KEY uq_users_username (username),
    CONSTRAINT fk_users_rol FOREIGN KEY (rol_id) REFERENCES roles (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CLIENTES TIENDA ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clientes (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(120)  NOT NULL,
    email       VARCHAR(120)  DEFAULT NULL,
    telefono    VARCHAR(20)   DEFAULT NULL,
    direccion   TEXT          DEFAULT NULL,
    password    VARCHAR(255)  DEFAULT NULL,
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CATEGORÍAS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)  NOT NULL,
    descripcion VARCHAR(255)  DEFAULT NULL,
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PRODUCTOS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    categoria_id    INT UNSIGNED   NOT NULL,
    nombre          VARCHAR(150)   NOT NULL,
    descripcion     TEXT           DEFAULT NULL,
    precio_base     DECIMAL(10,2)  DEFAULT NULL,
    tiene_variantes TINYINT(1)     NOT NULL DEFAULT 0,
    stock           INT            NOT NULL DEFAULT 0,
    codigo_barras   VARCHAR(100)   DEFAULT NULL,
    image_url       VARCHAR(255)   DEFAULT NULL,
    activo          TINYINT(1)     NOT NULL DEFAULT 1,
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_barras (codigo_barras),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── VARIANTES ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS producto_variantes (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    producto_id     INT UNSIGNED   NOT NULL,
    nombre          VARCHAR(100)   NOT NULL,
    precio          DECIMAL(10,2)  DEFAULT NULL,
    stock           INT            NOT NULL DEFAULT 0,
    codigo_barras   VARCHAR(100)   DEFAULT NULL,
    image_url       VARCHAR(255)   DEFAULT NULL,
    activo          TINYINT(1)     NOT NULL DEFAULT 1,
    orden           INT            NOT NULL DEFAULT 0,
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_variante_barras (codigo_barras),
    CONSTRAINT fk_variantes_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── VENTAS (CAJA) ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ventas (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    cliente_id      INT UNSIGNED   DEFAULT NULL,
    user_id         INT UNSIGNED   NOT NULL,
    metodo_pago     ENUM('Efectivo','Tarjeta','Transferencia') NOT NULL DEFAULT 'Efectivo',
    subtotal        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    descuento       DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    total           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    monto_recibido  DECIMAL(10,2)  DEFAULT NULL,
    cambio          DECIMAL(10,2)  DEFAULT NULL,
    nota            TEXT           DEFAULT NULL,
    correlativo     INT UNSIGNED   DEFAULT NULL,
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_ventas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id) ON DELETE SET NULL,
    CONSTRAINT fk_ventas_user    FOREIGN KEY (user_id)    REFERENCES users    (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DETALLE VENTAS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS venta_detalle (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    venta_id        INT UNSIGNED   NOT NULL,
    producto_id     INT UNSIGNED   NOT NULL,
    variante_id     INT UNSIGNED   DEFAULT NULL,
    nombre_producto VARCHAR(200)   NOT NULL,
    precio_unit     DECIMAL(10,2)  NOT NULL,
    cantidad        INT            NOT NULL DEFAULT 1,
    subtotal        DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_det_venta    FOREIGN KEY (venta_id)    REFERENCES ventas            (id) ON DELETE CASCADE,
    CONSTRAINT fk_det_producto FOREIGN KEY (producto_id) REFERENCES productos          (id),
    CONSTRAINT fk_det_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── ZONAS DE ENVÍO ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS zonas_envio (
    id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(100)   NOT NULL,
    costo      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    activo     TINYINT(1)     NOT NULL DEFAULT 1,
    created_at TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── PEDIDOS TIENDA ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedidos (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    codigo          CHAR(8)        NOT NULL,
    cliente_id      INT UNSIGNED   DEFAULT NULL,
    wa_numero       VARCHAR(20)    DEFAULT NULL,
    tipo_entrega    ENUM('Retiro','Envio') NOT NULL DEFAULT 'Retiro',
    direccion_envio TEXT           DEFAULT NULL,
    zona_id         INT UNSIGNED   DEFAULT NULL,
    estado          ENUM('Pendiente','En preparacion','Listo','En camino','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
    subtotal        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    costo_envio     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    total           DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    nota            TEXT           DEFAULT NULL,
    created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pedidos_codigo (codigo),
    CONSTRAINT fk_pedidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes   (id) ON DELETE SET NULL,
    CONSTRAINT fk_pedidos_zona    FOREIGN KEY (zona_id)    REFERENCES zonas_envio (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── DETALLE PEDIDOS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedido_detalle (
    id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    pedido_id       INT UNSIGNED   NOT NULL,
    producto_id     INT UNSIGNED   NOT NULL,
    variante_id     INT UNSIGNED   DEFAULT NULL,
    nombre_producto VARCHAR(200)   NOT NULL,
    precio_unit     DECIMAL(10,2)  NOT NULL,
    cantidad        INT            NOT NULL DEFAULT 1,
    subtotal        DECIMAL(10,2)  NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_pdet_pedido   FOREIGN KEY (pedido_id)   REFERENCES pedidos           (id) ON DELETE CASCADE,
    CONSTRAINT fk_pdet_producto FOREIGN KEY (producto_id) REFERENCES productos          (id),
    CONSTRAINT fk_pdet_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── HISTORIAL PEDIDOS ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS pedido_historial (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    pedido_id       INT UNSIGNED  NOT NULL,
    estado_anterior VARCHAR(50)   DEFAULT NULL,
    estado_nuevo    VARCHAR(50)   NOT NULL,
    user_id         INT UNSIGNED  DEFAULT NULL,
    nota            TEXT          DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_ph_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
    CONSTRAINT fk_ph_user   FOREIGN KEY (user_id)   REFERENCES users   (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── COMBOS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS combos (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(150)   NOT NULL,
    descripcion TEXT           DEFAULT NULL,
    imagen_url  VARCHAR(255)   DEFAULT NULL,
    descuento   DECIMAL(5,2)   DEFAULT NULL,
    activo      TINYINT(1)     NOT NULL DEFAULT 1,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── COMBO PRODUCTOS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS combo_productos (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    combo_id    INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    variante_id INT UNSIGNED DEFAULT NULL,
    cantidad    INT          NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_combo_producto (combo_id, producto_id, variante_id),
    CONSTRAINT fk_cp_combo    FOREIGN KEY (combo_id)    REFERENCES combos             (id) ON DELETE CASCADE,
    CONSTRAINT fk_cp_producto FOREIGN KEY (producto_id) REFERENCES productos          (id),
    CONSTRAINT fk_cp_variante FOREIGN KEY (variante_id) REFERENCES producto_variantes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── SERVICIOS DE CITAS ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS servicios_cita (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)   NOT NULL,
    descripcion TEXT           DEFAULT NULL,
    precio_base DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    duracion    INT            NOT NULL DEFAULT 60,
    activo      TINYINT(1)     NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CITAS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS citas (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    cliente_id  INT UNSIGNED   DEFAULT NULL,
    servicio_id INT UNSIGNED   NOT NULL,
    user_id     INT UNSIGNED   DEFAULT NULL,
    fecha       DATE           NOT NULL,
    hora_inicio TIME           NOT NULL,
    duracion    INT            NOT NULL DEFAULT 60,
    precio      DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    estado      ENUM('Pendiente','Confirmada','Completada','Cancelada') NOT NULL DEFAULT 'Pendiente',
    nota        TEXT           DEFAULT NULL,
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_citas_cliente  FOREIGN KEY (cliente_id)  REFERENCES clientes      (id) ON DELETE SET NULL,
    CONSTRAINT fk_citas_servicio FOREIGN KEY (servicio_id) REFERENCES servicios_cita (id),
    CONSTRAINT fk_citas_user     FOREIGN KEY (user_id)     REFERENCES users          (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── CONFIG CITAS ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS citas_config (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    horario_inicio       TIME         NOT NULL DEFAULT '08:00:00',
    horario_fin          TIME         NOT NULL DEFAULT '18:00:00',
    dias_laborales       VARCHAR(20)  NOT NULL DEFAULT '1,2,3,4,5,6',
    duracion_default     INT          NOT NULL DEFAULT 60,
    capacidad_simultanea INT          NOT NULL DEFAULT 1,
    updated_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── FACTURACIÓN CONFIG ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS facturacion_config (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nombre_fiscal    VARCHAR(150)  DEFAULT NULL,
    rtn              VARCHAR(20)   DEFAULT NULL,
    cai              VARCHAR(50)   DEFAULT NULL,
    rango_desde      VARCHAR(20)   DEFAULT NULL,
    rango_hasta      VARCHAR(20)   DEFAULT NULL,
    fecha_limite     DATE          DEFAULT NULL,
    establecimiento  VARCHAR(10)   DEFAULT NULL,
    punto_emision    VARCHAR(10)   DEFAULT NULL,
    direccion_fiscal TEXT          DEFAULT NULL,
    correlativo      INT           NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── BANNERS ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS banners (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    titulo     VARCHAR(150)  DEFAULT NULL,
    imagen_url VARCHAR(255)  NOT NULL,
    enlace     VARCHAR(255)  DEFAULT NULL,
    orden      INT           NOT NULL DEFAULT 0,
    activo     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── GALERÍA CLIENTES ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS galeria_clientes (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    imagen_url  VARCHAR(255)  NOT NULL,
    descripcion VARCHAR(255)  DEFAULT NULL,
    orden       INT           NOT NULL DEFAULT 0,
    activo      TINYINT(1)    NOT NULL DEFAULT 1,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- ── ROL ADMIN ─────────────────────────────────────────────────
INSERT INTO roles (id, nombre, slug, descripcion) VALUES
(1, 'Administrador', 'admin', 'Acceso total al sistema');

-- ── PERMISOS ─────────────────────────────────────────────────
INSERT INTO permissions (nombre, slug, modulo) VALUES
-- Categorías
('Ver categorías',    'categorias.ver',     'categorias'),
('Crear categorías',  'categorias.crear',   'categorias'),
('Editar categorías', 'categorias.editar',  'categorias'),
('Eliminar categorías','categorias.eliminar','categorias'),
-- Productos
('Ver productos',     'productos.ver',      'productos'),
('Crear productos',   'productos.crear',    'productos'),
('Editar productos',  'productos.editar',   'productos'),
('Eliminar productos','productos.eliminar', 'productos'),
-- Combos
('Ver combos',        'combos.ver',         'combos'),
('Crear combos',      'combos.crear',       'combos'),
('Editar combos',     'combos.editar',      'combos'),
('Eliminar combos',   'combos.eliminar',    'combos'),
-- Ventas
('Ver ventas',        'ventas.ver',         'ventas'),
('Crear ventas',      'ventas.crear',       'ventas'),
('Eliminar ventas',   'ventas.eliminar',    'ventas'),
-- Pedidos
('Ver pedidos',       'pedidos.ver',        'pedidos'),
('Crear pedidos',     'pedidos.crear',      'pedidos'),
('Gestionar pedidos', 'pedidos.gestionar',  'pedidos'),
('Eliminar pedidos',  'pedidos.eliminar',   'pedidos'),
-- Clientes
('Ver clientes',      'clientes.ver',       'clientes'),
('Crear clientes',    'clientes.crear',     'clientes'),
-- Citas
('Ver citas',         'citas.ver',          'citas'),
('Crear citas',       'citas.crear',        'citas'),
('Editar citas',      'citas.editar',       'citas'),
('Eliminar citas',    'citas.eliminar',     'citas'),
-- Servicios
('Ver servicios',     'servicios.ver',      'citas'),
('Crear servicios',   'servicios.crear',    'citas'),
('Editar servicios',  'servicios.editar',   'citas'),
('Eliminar servicios','servicios.eliminar', 'citas'),
-- Facturación
('Ver facturación',   'facturacion.ver',    'facturacion'),
('Crear facturas',    'facturacion.crear',  'facturacion'),
('Configurar facturación','facturacion.configurar','facturacion'),
-- Reportes
('Ver reportes',      'reportes.ver',       'reportes'),
('Exportar reportes', 'reportes.exportar',  'reportes'),
-- Usuarios
('Ver usuarios',      'usuarios.ver',       'usuarios'),
('Crear usuarios',    'usuarios.crear',     'usuarios'),
('Editar usuarios',   'usuarios.editar',    'usuarios'),
('Eliminar usuarios', 'usuarios.eliminar',  'usuarios'),
-- Roles
('Ver roles',         'roles.ver',          'roles'),
('Crear roles',       'roles.crear',        'roles'),
('Editar roles',      'roles.editar',       'roles'),
('Eliminar roles',    'roles.eliminar',     'roles'),
-- Tienda
('Ver tienda',        'tienda.ver',         'tienda'),
('Configurar tienda', 'tienda.configurar',  'tienda'),
('Ver banners',       'banners.ver',        'tienda'),
('Gestionar banners', 'banners.gestionar',  'tienda'),
('Ver galería',       'galeria.ver',        'tienda'),
('Gestionar galería', 'galeria.gestionar',  'tienda'),
('Ver zonas',         'zonas.ver',          'tienda'),
('Gestionar zonas',   'zonas.gestionar',    'tienda');

-- ── ASIGNAR TODOS LOS PERMISOS AL ROL ADMIN ──────────────────
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permissions;

-- ── USUARIO ANA MARCOL ────────────────────────────────────────
-- Contraseña: Ana2026! (bcrypt)
INSERT INTO users (id, nombre, username, email, password, rol_id, activo, tour_completado) VALUES
(1, 'Ana Marcol', 'anamarcol', 'ana@anamarcol.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 1, 1, 0);

-- ── CONFIG FACTURACIÓN ────────────────────────────────────────
INSERT INTO facturacion_config (id, nombre_fiscal, rtn, cai, rango_desde, rango_hasta,
    fecha_limite, establecimiento, punto_emision, direccion_fiscal, correlativo)
VALUES (1,
    'ANA MARCOL MAKEUP STUDIO',
    '16012001003960',
    '22E397-65F31F-2EE8E0-63BE03-09091A-42',
    '000-001-01-00003001',
    '000-001-01-00006000',
    '2025-09-24',
    '000',
    '001',
    'Barrio Abajo Avenida La Libertad, 1 cuadra abajo de Banco Atlántida SB, Tegucigalpa',
    5740);

-- ── CONFIG CITAS ──────────────────────────────────────────────
INSERT INTO citas_config (id, horario_inicio, horario_fin, dias_laborales, duracion_default, capacidad_simultanea)
VALUES (1, '08:00:00', '18:00:00', '1,2,3,4,5,6', 60, 1);

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

-- ── ROLES ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_roles_findAll;
DELIMITER $$
CREATE PROCEDURE sp_roles_findAll()
BEGIN
    SELECT id, nombre, slug, descripcion, activo FROM roles ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_findById;
DELIMITER $$
CREATE PROCEDURE sp_roles_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, slug, descripcion, activo FROM roles WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_findBySlug;
DELIMITER $$
CREATE PROCEDURE sp_roles_findBySlug(IN p_slug VARCHAR(60))
BEGIN
    SELECT id, nombre, slug, descripcion, activo FROM roles WHERE slug=p_slug LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_getPermissions;
DELIMITER $$
CREATE PROCEDURE sp_roles_getPermissions(IN p_rol_id INT)
BEGIN
    SELECT p.id, p.nombre, p.slug, p.modulo
    FROM permissions p
    INNER JOIN rol_permisos rp ON rp.permiso_id=p.id
    WHERE rp.rol_id=p_rol_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_insert;
DELIMITER $$
CREATE PROCEDURE sp_roles_insert(IN p_nombre VARCHAR(60), IN p_slug VARCHAR(60), IN p_descripcion VARCHAR(255))
BEGIN
    INSERT INTO roles (nombre, slug, descripcion) VALUES (p_nombre, p_slug, p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_update;
DELIMITER $$
CREATE PROCEDURE sp_roles_update(IN p_id INT, IN p_nombre VARCHAR(60), IN p_slug VARCHAR(60), IN p_descripcion VARCHAR(255))
BEGIN
    UPDATE roles SET nombre=p_nombre, slug=p_slug, descripcion=p_descripcion WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_delete;
DELIMITER $$
CREATE PROCEDURE sp_roles_delete(IN p_id INT)
BEGIN
    DELETE FROM roles WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_slugExists;
DELIMITER $$
CREATE PROCEDURE sp_roles_slugExists(IN p_slug VARCHAR(60), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) AS existe FROM roles WHERE slug=p_slug AND id!=p_exclude_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_hasUsers;
DELIMITER $$
CREATE PROCEDURE sp_roles_hasUsers(IN p_rol_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM users WHERE rol_id=p_rol_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_count;
DELIMITER $$
CREATE PROCEDURE sp_roles_count()
BEGIN
    SELECT COUNT(*) AS total FROM roles;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_assignPermission;
DELIMITER $$
CREATE PROCEDURE sp_roles_assignPermission(IN p_rol_id INT, IN p_permiso_id INT)
BEGIN
    INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES (p_rol_id, p_permiso_id);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_revokePermission;
DELIMITER $$
CREATE PROCEDURE sp_roles_revokePermission(IN p_rol_id INT, IN p_permiso_id INT)
BEGIN
    DELETE FROM rol_permisos WHERE rol_id=p_rol_id AND permiso_id=p_permiso_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_roles_revokeAllPermissions;
DELIMITER $$
CREATE PROCEDURE sp_roles_revokeAllPermissions(IN p_rol_id INT)
BEGIN
    DELETE FROM rol_permisos WHERE rol_id=p_rol_id;
END$$
DELIMITER ;

-- ── PERMISOS ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_permissions_findAll;
DELIMITER $$
CREATE PROCEDURE sp_permissions_findAll()
BEGIN
    SELECT id, nombre, slug, modulo, descripcion FROM permissions ORDER BY modulo, nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_findById;
DELIMITER $$
CREATE PROCEDURE sp_permissions_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, slug, modulo, descripcion FROM permissions WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_findByModule;
DELIMITER $$
CREATE PROCEDURE sp_permissions_findByModule(IN p_modulo VARCHAR(60))
BEGIN
    SELECT id, nombre, slug, modulo, descripcion FROM permissions WHERE modulo=p_modulo ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_getModules;
DELIMITER $$
CREATE PROCEDURE sp_permissions_getModules()
BEGIN
    SELECT DISTINCT modulo FROM permissions ORDER BY modulo ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_count;
DELIMITER $$
CREATE PROCEDURE sp_permissions_count()
BEGIN
    SELECT COUNT(*) AS total FROM permissions;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_slugExists;
DELIMITER $$
CREATE PROCEDURE sp_permissions_slugExists(IN p_slug VARCHAR(100), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) AS existe FROM permissions WHERE slug=p_slug AND id!=p_exclude_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_isAssigned;
DELIMITER $$
CREATE PROCEDURE sp_permissions_isAssigned(IN p_id INT)
BEGIN
    SELECT COUNT(*) AS total FROM rol_permisos WHERE permiso_id=p_id;
END$$
DELIMITER ;

-- ── USUARIOS ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_users_findAll;
DELIMITER $$
CREATE PROCEDURE sp_users_findAll()
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id,
           u.activo, u.foto, u.telefono, u.tour_completado, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id ORDER BY u.nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_findById;
DELIMITER $$
CREATE PROCEDURE sp_users_findById(IN p_id INT)
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id,
           u.activo, u.foto, u.telefono, u.tour_completado, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id WHERE u.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_findByEmail;
DELIMITER $$
CREATE PROCEDURE sp_users_findByEmail(IN p_email VARCHAR(120))
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id,
           u.activo, u.foto, u.telefono, u.tour_completado, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id WHERE u.email=p_email LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_findByUsername;
DELIMITER $$
CREATE PROCEDURE sp_users_findByUsername(IN p_username VARCHAR(60))
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.password, u.rol_id,
           u.activo, u.foto, u.telefono, u.tour_completado, u.created_at, u.updated_at,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id WHERE u.username=p_username LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_insert;
DELIMITER $$
CREATE PROCEDURE sp_users_insert(
    IN p_nombre VARCHAR(120), IN p_username VARCHAR(60), IN p_email VARCHAR(120),
    IN p_password VARCHAR(255), IN p_rol_id INT, IN p_activo TINYINT,
    IN p_foto VARCHAR(255), IN p_telefono VARCHAR(20)
)
BEGIN
    INSERT INTO users (nombre, username, email, password, rol_id, activo, foto, telefono)
    VALUES (p_nombre, p_username, p_email, p_password, p_rol_id, p_activo, p_foto, p_telefono);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_update;
DELIMITER $$
CREATE PROCEDURE sp_users_update(
    IN p_id INT, IN p_nombre VARCHAR(120), IN p_username VARCHAR(60),
    IN p_email VARCHAR(120), IN p_rol_id INT, IN p_activo TINYINT,
    IN p_foto VARCHAR(255), IN p_telefono VARCHAR(20)
)
BEGIN
    UPDATE users SET nombre=p_nombre, username=p_username, email=p_email,
        rol_id=p_rol_id, activo=p_activo, foto=COALESCE(p_foto, foto), telefono=p_telefono
    WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_updatePassword;
DELIMITER $$
CREATE PROCEDURE sp_users_updatePassword(IN p_id INT, IN p_password VARCHAR(255))
BEGIN
    UPDATE users SET password=p_password WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_users_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE users SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_delete;
DELIMITER $$
CREATE PROCEDURE sp_users_delete(IN p_id INT)
BEGIN
    DELETE FROM users WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_emailExists;
DELIMITER $$
CREATE PROCEDURE sp_users_emailExists(IN p_email VARCHAR(120), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) AS existe FROM users WHERE email=p_email AND id!=p_exclude_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_usernameExists;
DELIMITER $$
CREATE PROCEDURE sp_users_usernameExists(IN p_username VARCHAR(60), IN p_exclude_id INT)
BEGIN
    SELECT COUNT(*) AS existe FROM users WHERE username=p_username AND id!=p_exclude_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_count;
DELIMITER $$
CREATE PROCEDURE sp_users_count()
BEGIN
    SELECT COUNT(*) AS total FROM users;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_countActivos;
DELIMITER $$
CREATE PROCEDURE sp_users_countActivos()
BEGIN
    SELECT COUNT(*) AS total FROM users WHERE activo=1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_marcarTour;
DELIMITER $$
CREATE PROCEDURE sp_users_marcarTour(IN p_id INT)
BEGIN
    UPDATE users SET tour_completado=1 WHERE id=p_id;
END$$
DELIMITER ;

-- ── CLIENTES ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_clientes_findAll;
DELIMITER $$
CREATE PROCEDURE sp_clientes_findAll()
BEGIN
    SELECT id, nombre, email, telefono, direccion, activo, created_at FROM clientes ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_findById;
DELIMITER $$
CREATE PROCEDURE sp_clientes_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, email, telefono, direccion, password, activo, created_at FROM clientes WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_findByEmail;
DELIMITER $$
CREATE PROCEDURE sp_clientes_findByEmail(IN p_email VARCHAR(120))
BEGIN
    SELECT id, nombre, email, telefono, direccion, password, activo, created_at FROM clientes WHERE email=p_email LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_search;
DELIMITER $$
CREATE PROCEDURE sp_clientes_search(IN p_query VARCHAR(120))
BEGIN
    SELECT id, nombre, email, telefono FROM clientes
    WHERE activo=1 AND (nombre LIKE CONCAT('%',p_query,'%') OR telefono LIKE CONCAT('%',p_query,'%'))
    LIMIT 10;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_insert;
DELIMITER $$
CREATE PROCEDURE sp_clientes_insert(
    IN p_nombre VARCHAR(120), IN p_email VARCHAR(120),
    IN p_telefono VARCHAR(20), IN p_password VARCHAR(255)
)
BEGIN
    INSERT INTO clientes (nombre, email, telefono, password) VALUES (p_nombre, p_email, p_telefono, p_password);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_clientes_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE clientes SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_count;
DELIMITER $$
CREATE PROCEDURE sp_clientes_count()
BEGIN
    SELECT COUNT(*) AS total FROM clientes;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_clientes_emailExists;
DELIMITER $$
CREATE PROCEDURE sp_clientes_emailExists(IN p_email VARCHAR(120))
BEGIN
    SELECT COUNT(*) AS existe FROM clientes WHERE email=p_email;
END$$
DELIMITER ;

-- ── CATEGORÍAS ───────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_categorias_findAll;
DELIMITER $$
CREATE PROCEDURE sp_categorias_findAll()
BEGIN
    SELECT id, nombre, descripcion, activo, created_at FROM categorias ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_findById;
DELIMITER $$
CREATE PROCEDURE sp_categorias_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, activo FROM categorias WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_insert;
DELIMITER $$
CREATE PROCEDURE sp_categorias_insert(IN p_nombre VARCHAR(100), IN p_descripcion VARCHAR(255))
BEGIN
    INSERT INTO categorias (nombre, descripcion) VALUES (p_nombre, p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_update;
DELIMITER $$
CREATE PROCEDURE sp_categorias_update(IN p_id INT, IN p_nombre VARCHAR(100), IN p_descripcion VARCHAR(255))
BEGIN
    UPDATE categorias SET nombre=p_nombre, descripcion=p_descripcion WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_categorias_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE categorias SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_delete;
DELIMITER $$
CREATE PROCEDURE sp_categorias_delete(IN p_id INT)
BEGIN
    UPDATE categorias SET activo=0 WHERE id=p_id;
END$$
DELIMITER ;

-- ── PRODUCTOS ────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_productos_findAll;
DELIMITER $$
CREATE PROCEDURE sp_productos_findAll()
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_base,
           p.tiene_variantes, p.stock, p.codigo_barras, p.image_url, p.activo,
           p.created_at, p.updated_at, c.nombre AS categoria_nombre
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id ORDER BY p.nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_findActivos;
DELIMITER $$
CREATE PROCEDURE sp_productos_findActivos()
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_base,
           p.tiene_variantes, p.stock, p.codigo_barras, p.image_url, p.activo,
           c.nombre AS categoria_nombre
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.activo=1 ORDER BY p.nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_findById;
DELIMITER $$
CREATE PROCEDURE sp_productos_findById(IN p_id INT)
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_base,
           p.tiene_variantes, p.stock, p.codigo_barras, p.image_url, p.activo,
           p.created_at, p.updated_at, c.nombre AS categoria_nombre
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id WHERE p.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_findByBarras;
DELIMITER $$
CREATE PROCEDURE sp_productos_findByBarras(IN p_barras VARCHAR(100))
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.precio_base, p.tiene_variantes,
           p.stock, p.image_url, p.activo, c.nombre AS categoria_nombre
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.codigo_barras=p_barras AND p.activo=1 LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_search;
DELIMITER $$
CREATE PROCEDURE sp_productos_search(IN p_query VARCHAR(150))
BEGIN
    SELECT p.id, p.nombre, p.precio_base, p.tiene_variantes, p.stock, p.image_url,
           c.nombre AS categoria_nombre
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.activo=1 AND (p.nombre LIKE CONCAT('%',p_query,'%') OR p.codigo_barras LIKE CONCAT('%',p_query,'%'))
    LIMIT 20;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_insert;
DELIMITER $$
CREATE PROCEDURE sp_productos_insert(
    IN p_categoria_id INT, IN p_nombre VARCHAR(150), IN p_descripcion TEXT,
    IN p_precio_base DECIMAL(10,2), IN p_tiene_variantes TINYINT,
    IN p_stock INT, IN p_codigo_barras VARCHAR(100), IN p_image_url VARCHAR(255)
)
BEGIN
    INSERT INTO productos (categoria_id, nombre, descripcion, precio_base, tiene_variantes, stock, codigo_barras, image_url)
    VALUES (p_categoria_id, p_nombre, p_descripcion, p_precio_base, p_tiene_variantes, p_stock, p_codigo_barras, p_image_url);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_update;
DELIMITER $$
CREATE PROCEDURE sp_productos_update(
    IN p_id INT, IN p_categoria_id INT, IN p_nombre VARCHAR(150), IN p_descripcion TEXT,
    IN p_precio_base DECIMAL(10,2), IN p_stock INT, IN p_codigo_barras VARCHAR(100), IN p_image_url VARCHAR(255)
)
BEGIN
    UPDATE productos SET categoria_id=p_categoria_id, nombre=p_nombre, descripcion=p_descripcion,
        precio_base=p_precio_base, stock=p_stock, codigo_barras=p_codigo_barras,
        image_url=COALESCE(p_image_url, image_url)
    WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_productos_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE productos SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_delete;
DELIMITER $$
CREATE PROCEDURE sp_productos_delete(IN p_id INT)
BEGIN
    UPDATE productos SET activo=0 WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_count;
DELIMITER $$
CREATE PROCEDURE sp_productos_count()
BEGIN
    SELECT COUNT(*) AS total FROM productos WHERE activo=1;
END$$
DELIMITER ;

-- ── VARIANTES ────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_productos_findVariantes;
DELIMITER $$
CREATE PROCEDURE sp_productos_findVariantes(IN p_producto_id INT)
BEGIN
    SELECT id, producto_id, nombre, precio, stock, codigo_barras, image_url, activo, orden
    FROM producto_variantes WHERE producto_id=p_producto_id ORDER BY orden ASC, nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_findByBarras;
DELIMITER $$
CREATE PROCEDURE sp_variantes_findByBarras(IN p_barras VARCHAR(100))
BEGIN
    SELECT v.id, v.producto_id, v.nombre, v.precio, v.stock, v.image_url, v.activo,
           p.nombre AS producto_nombre, p.image_url AS producto_imagen
    FROM producto_variantes v INNER JOIN productos p ON p.id=v.producto_id
    WHERE v.codigo_barras=p_barras AND v.activo=1 LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_insert;
DELIMITER $$
CREATE PROCEDURE sp_variantes_insert(
    IN p_producto_id INT, IN p_nombre VARCHAR(100), IN p_precio DECIMAL(10,2),
    IN p_stock INT, IN p_codigo_barras VARCHAR(100), IN p_image_url VARCHAR(255), IN p_orden INT
)
BEGIN
    INSERT INTO producto_variantes (producto_id, nombre, precio, stock, codigo_barras, image_url, orden)
    VALUES (p_producto_id, p_nombre, p_precio, p_stock, p_codigo_barras, p_image_url, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_update;
DELIMITER $$
CREATE PROCEDURE sp_variantes_update(
    IN p_id INT, IN p_nombre VARCHAR(100), IN p_precio DECIMAL(10,2),
    IN p_stock INT, IN p_codigo_barras VARCHAR(100), IN p_image_url VARCHAR(255), IN p_orden INT
)
BEGIN
    UPDATE producto_variantes SET nombre=p_nombre, precio=p_precio, stock=p_stock,
        codigo_barras=p_codigo_barras, image_url=COALESCE(p_image_url, image_url), orden=p_orden
    WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_variantes_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE producto_variantes SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_delete;
DELIMITER $$
CREATE PROCEDURE sp_variantes_delete(IN p_id INT)
BEGIN
    DELETE FROM producto_variantes WHERE id=p_id;
END$$
DELIMITER ;

-- ── VENTAS ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_ventas_findAll;
DELIMITER $$
CREATE PROCEDURE sp_ventas_findAll()
BEGIN
    SELECT v.id, v.cliente_id, v.user_id, v.metodo_pago, v.subtotal, v.descuento,
           v.total, v.monto_recibido, v.cambio, v.nota, v.correlativo, v.created_at,
           c.nombre AS cliente_nombre, u.nombre AS cajero_nombre
    FROM ventas v
    LEFT  JOIN clientes c ON c.id=v.cliente_id
    INNER JOIN users    u ON u.id=v.user_id
    ORDER BY v.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_findById;
DELIMITER $$
CREATE PROCEDURE sp_ventas_findById(IN p_id INT)
BEGIN
    SELECT v.id, v.cliente_id, v.user_id, v.metodo_pago, v.subtotal, v.descuento,
           v.total, v.monto_recibido, v.cambio, v.nota, v.correlativo, v.created_at,
           c.nombre AS cliente_nombre, u.nombre AS cajero_nombre
    FROM ventas v
    LEFT  JOIN clientes c ON c.id=v.cliente_id
    INNER JOIN users    u ON u.id=v.user_id
    WHERE v.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_findDetalle;
DELIMITER $$
CREATE PROCEDURE sp_ventas_findDetalle(IN p_venta_id INT)
BEGIN
    SELECT id, venta_id, producto_id, variante_id, nombre_producto, precio_unit, cantidad, subtotal
    FROM venta_detalle WHERE venta_id=p_venta_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_insert;
DELIMITER $$
CREATE PROCEDURE sp_ventas_insert(
    IN p_cliente_id INT, IN p_user_id INT, IN p_metodo_pago VARCHAR(20),
    IN p_subtotal DECIMAL(10,2), IN p_descuento DECIMAL(10,2), IN p_total DECIMAL(10,2),
    IN p_monto_recibido DECIMAL(10,2), IN p_cambio DECIMAL(10,2), IN p_nota TEXT
)
BEGIN
    DECLARE v_correlativo INT;
    SELECT correlativo INTO v_correlativo FROM facturacion_config WHERE id=1 FOR UPDATE;
    INSERT INTO ventas (cliente_id, user_id, metodo_pago, subtotal, descuento, total,
        monto_recibido, cambio, nota, correlativo)
    VALUES (p_cliente_id, p_user_id, p_metodo_pago, p_subtotal, p_descuento, p_total,
        p_monto_recibido, p_cambio, p_nota, v_correlativo);
    UPDATE facturacion_config SET correlativo=correlativo+1 WHERE id=1;
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_insertDetalle;
DELIMITER $$
CREATE PROCEDURE sp_ventas_insertDetalle(
    IN p_venta_id INT, IN p_producto_id INT, IN p_variante_id INT,
    IN p_nombre_producto VARCHAR(200), IN p_precio_unit DECIMAL(10,2),
    IN p_cantidad INT, IN p_subtotal DECIMAL(10,2)
)
BEGIN
    INSERT INTO venta_detalle (venta_id, producto_id, variante_id, nombre_producto, precio_unit, cantidad, subtotal)
    VALUES (p_venta_id, p_producto_id, p_variante_id, p_nombre_producto, p_precio_unit, p_cantidad, p_subtotal);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_countHoy;
DELIMITER $$
CREATE PROCEDURE sp_ventas_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM ventas WHERE DATE(created_at)=CURDATE();
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_totalHoy;
DELIMITER $$
CREATE PROCEDURE sp_ventas_totalHoy()
BEGIN
    SELECT COALESCE(SUM(total),0) AS total FROM ventas WHERE DATE(created_at)=CURDATE();
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_ventas_findByCliente;
DELIMITER $$
CREATE PROCEDURE sp_ventas_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT v.id, v.metodo_pago, v.total, v.created_at
    FROM ventas v WHERE v.cliente_id=p_cliente_id ORDER BY v.created_at DESC;
END$$
DELIMITER ;

-- ── PEDIDOS ──────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pedidos_findAll;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findAll()
BEGIN
    SELECT p.id, p.codigo, p.cliente_id, p.wa_numero, p.tipo_entrega, p.direccion_envio,
           p.zona_id, p.estado, p.subtotal, p.costo_envio, p.total, p.nota, p.created_at, p.updated_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p LEFT JOIN clientes c ON c.id=p.cliente_id ORDER BY p.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_findById;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findById(IN p_id INT)
BEGIN
    SELECT p.id, p.codigo, p.cliente_id, p.wa_numero, p.tipo_entrega, p.direccion_envio,
           p.zona_id, p.estado, p.subtotal, p.costo_envio, p.total, p.nota, p.created_at, p.updated_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p LEFT JOIN clientes c ON c.id=p.cliente_id WHERE p.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_findByEstado;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findByEstado(IN p_estado VARCHAR(30))
BEGIN
    SELECT p.id, p.codigo, p.cliente_id, p.wa_numero, p.tipo_entrega, p.estado,
           p.subtotal, p.costo_envio, p.total, p.created_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p LEFT JOIN clientes c ON c.id=p.cliente_id
    WHERE p.estado=p_estado ORDER BY p.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_findDetalle;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findDetalle(IN p_pedido_id INT)
BEGIN
    SELECT id, pedido_id, producto_id, variante_id, nombre_producto, precio_unit, cantidad, subtotal
    FROM pedido_detalle WHERE pedido_id=p_pedido_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_findHistorial;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findHistorial(IN p_pedido_id INT)
BEGIN
    SELECT h.id, h.pedido_id, h.estado_anterior, h.estado_nuevo, h.nota, h.created_at,
           u.nombre AS usuario_nombre
    FROM pedido_historial h LEFT JOIN users u ON u.id=h.user_id
    WHERE h.pedido_id=p_pedido_id ORDER BY h.created_at ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_findByCliente;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT p.id, p.codigo, p.tipo_entrega, p.estado, p.total, p.costo_envio,
           p.subtotal, p.wa_numero, p.created_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono
    FROM pedidos p LEFT JOIN clientes c ON c.id=p.cliente_id
    WHERE p.cliente_id=p_cliente_id ORDER BY p.created_at DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_insert;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_insert(
    IN p_codigo CHAR(8), IN p_cliente_id INT, IN p_wa_numero VARCHAR(20),
    IN p_tipo_entrega VARCHAR(10), IN p_direccion_envio TEXT, IN p_zona_id INT,
    IN p_subtotal DECIMAL(10,2), IN p_costo_envio DECIMAL(10,2), IN p_total DECIMAL(10,2), IN p_nota TEXT
)
BEGIN
    INSERT INTO pedidos (codigo, cliente_id, wa_numero, tipo_entrega, direccion_envio,
        zona_id, subtotal, costo_envio, total, nota)
    VALUES (p_codigo, p_cliente_id, p_wa_numero, p_tipo_entrega, p_direccion_envio,
        p_zona_id, p_subtotal, p_costo_envio, p_total, p_nota);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_insertDetalle;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_insertDetalle(
    IN p_pedido_id INT, IN p_producto_id INT, IN p_variante_id INT,
    IN p_nombre_producto VARCHAR(200), IN p_precio_unit DECIMAL(10,2),
    IN p_cantidad INT, IN p_subtotal DECIMAL(10,2)
)
BEGIN
    INSERT INTO pedido_detalle (pedido_id, producto_id, variante_id, nombre_producto, precio_unit, cantidad, subtotal)
    VALUES (p_pedido_id, p_producto_id, p_variante_id, p_nombre_producto, p_precio_unit, p_cantidad, p_subtotal);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_updateEstado;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_updateEstado(IN p_id INT, IN p_estado VARCHAR(30), IN p_user_id INT, IN p_nota TEXT)
BEGIN
    DECLARE v_estado_anterior VARCHAR(50);
    SELECT estado INTO v_estado_anterior FROM pedidos WHERE id=p_id;
    UPDATE pedidos SET estado=p_estado WHERE id=p_id;
    INSERT INTO pedido_historial (pedido_id, estado_anterior, estado_nuevo, user_id, nota)
    VALUES (p_id, v_estado_anterior, p_estado, p_user_id, p_nota);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_existeCodigo;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_existeCodigo(IN p_codigo CHAR(8))
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE codigo=p_codigo;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_countByEstado;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_countByEstado(IN p_estado VARCHAR(30))
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE estado=p_estado;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_countHoy;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE DATE(created_at)=CURDATE();
END$$
DELIMITER ;

-- ── COMBOS ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_combos_findAll;
DELIMITER $$
CREATE PROCEDURE sp_combos_findAll()
BEGIN
    SELECT id, nombre, descripcion, imagen_url, descuento, activo, created_at FROM combos ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_findActivos;
DELIMITER $$
CREATE PROCEDURE sp_combos_findActivos()
BEGIN
    SELECT id, nombre, descripcion, imagen_url, descuento, activo FROM combos WHERE activo=1 ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_findById;
DELIMITER $$
CREATE PROCEDURE sp_combos_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, imagen_url, descuento, activo, created_at FROM combos WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_findProductos;
DELIMITER $$
CREATE PROCEDURE sp_combos_findProductos(IN p_combo_id INT)
BEGIN
    SELECT cp.id, cp.combo_id, cp.producto_id, cp.variante_id, cp.cantidad,
           p.nombre AS producto_nombre, p.precio_base, p.image_url AS producto_imagen,
           v.nombre AS variante_nombre, COALESCE(v.precio, p.precio_base) AS precio_unitario
    FROM combo_productos cp
    INNER JOIN productos p ON p.id=cp.producto_id
    LEFT  JOIN producto_variantes v ON v.id=cp.variante_id
    WHERE cp.combo_id=p_combo_id ORDER BY p.nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_insert;
DELIMITER $$
CREATE PROCEDURE sp_combos_insert(IN p_nombre VARCHAR(150), IN p_descripcion TEXT, IN p_imagen_url VARCHAR(255), IN p_descuento DECIMAL(5,2))
BEGIN
    INSERT INTO combos (nombre, descripcion, imagen_url, descuento) VALUES (p_nombre, p_descripcion, p_imagen_url, p_descuento);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_update;
DELIMITER $$
CREATE PROCEDURE sp_combos_update(IN p_id INT, IN p_nombre VARCHAR(150), IN p_descripcion TEXT, IN p_imagen_url VARCHAR(255), IN p_descuento DECIMAL(5,2))
BEGIN
    UPDATE combos SET nombre=p_nombre, descripcion=p_descripcion,
        imagen_url=COALESCE(p_imagen_url, imagen_url), descuento=p_descuento WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_combos_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE combos SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_delete;
DELIMITER $$
CREATE PROCEDURE sp_combos_delete(IN p_id INT)
BEGIN
    UPDATE combos SET activo=0 WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_addProducto;
DELIMITER $$
CREATE PROCEDURE sp_combos_addProducto(IN p_combo_id INT, IN p_producto_id INT, IN p_variante_id INT, IN p_cantidad INT)
BEGIN
    INSERT INTO combo_productos (combo_id, producto_id, variante_id, cantidad)
    VALUES (p_combo_id, p_producto_id, p_variante_id, p_cantidad)
    ON DUPLICATE KEY UPDATE cantidad=p_cantidad;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_clearProductos;
DELIMITER $$
CREATE PROCEDURE sp_combos_clearProductos(IN p_combo_id INT)
BEGIN
    DELETE FROM combo_productos WHERE combo_id=p_combo_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_count;
DELIMITER $$
CREATE PROCEDURE sp_combos_count()
BEGIN
    SELECT COUNT(*) AS total FROM combos;
END$$
DELIMITER ;

-- ── SERVICIOS ────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_servicios_findAll;
DELIMITER $$
CREATE PROCEDURE sp_servicios_findAll()
BEGIN
    SELECT id, nombre, descripcion, precio_base, duracion, activo FROM servicios_cita ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_findActivos;
DELIMITER $$
CREATE PROCEDURE sp_servicios_findActivos()
BEGIN
    SELECT id, nombre, descripcion, precio_base, duracion, activo FROM servicios_cita WHERE activo=1 ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_findById;
DELIMITER $$
CREATE PROCEDURE sp_servicios_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, descripcion, precio_base, duracion, activo FROM servicios_cita WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_insert;
DELIMITER $$
CREATE PROCEDURE sp_servicios_insert(IN p_nombre VARCHAR(100), IN p_descripcion TEXT, IN p_precio_base DECIMAL(10,2), IN p_duracion INT)
BEGIN
    INSERT INTO servicios_cita (nombre, descripcion, precio_base, duracion) VALUES (p_nombre, p_descripcion, p_precio_base, p_duracion);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_update;
DELIMITER $$
CREATE PROCEDURE sp_servicios_update(IN p_id INT, IN p_nombre VARCHAR(100), IN p_descripcion TEXT, IN p_precio_base DECIMAL(10,2), IN p_duracion INT)
BEGIN
    UPDATE servicios_cita SET nombre=p_nombre, descripcion=p_descripcion, precio_base=p_precio_base, duracion=p_duracion WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_servicios_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE servicios_cita SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_servicios_delete;
DELIMITER $$
CREATE PROCEDURE sp_servicios_delete(IN p_id INT)
BEGIN
    UPDATE servicios_cita SET activo=0 WHERE id=p_id;
END$$
DELIMITER ;

-- ── CITAS ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_getConfig;
DELIMITER $$
CREATE PROCEDURE sp_citas_getConfig()
BEGIN
    SELECT id, horario_inicio, horario_fin, dias_laborales, duracion_default, capacidad_simultanea FROM citas_config WHERE id=1 LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_updateConfig;
DELIMITER $$
CREATE PROCEDURE sp_citas_updateConfig(
    IN p_horario_inicio TIME, IN p_horario_fin TIME, IN p_dias_laborales VARCHAR(20),
    IN p_duracion_default INT, IN p_capacidad_simultanea INT
)
BEGIN
    UPDATE citas_config SET horario_inicio=p_horario_inicio, horario_fin=p_horario_fin,
        dias_laborales=p_dias_laborales, duracion_default=p_duracion_default,
        capacidad_simultanea=p_capacidad_simultanea WHERE id=1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findAll;
DELIMITER $$
CREATE PROCEDURE sp_citas_findAll()
BEGIN
    SELECT ci.id, ci.cliente_id, ci.servicio_id, ci.user_id, ci.fecha, ci.hora_inicio,
           ci.duracion, ci.precio, ci.estado, ci.nota, ci.created_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           s.nombre AS servicio_nombre, u.nombre AS empleado_nombre
    FROM citas ci
    LEFT  JOIN clientes      c ON c.id=ci.cliente_id
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    LEFT  JOIN users          u ON u.id=ci.user_id
    ORDER BY ci.fecha ASC, ci.hora_inicio ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findByFecha;
DELIMITER $$
CREATE PROCEDURE sp_citas_findByFecha(IN p_fecha DATE)
BEGIN
    SELECT ci.id, ci.cliente_id, ci.servicio_id, ci.user_id, ci.fecha, ci.hora_inicio,
           ci.duracion, ci.precio, ci.estado, ci.nota, ci.created_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           s.nombre AS servicio_nombre, u.nombre AS empleado_nombre
    FROM citas ci
    LEFT  JOIN clientes      c ON c.id=ci.cliente_id
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    LEFT  JOIN users          u ON u.id=ci.user_id
    WHERE ci.fecha=p_fecha AND ci.estado!='Cancelada' ORDER BY ci.hora_inicio ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findByMes;
DELIMITER $$
CREATE PROCEDURE sp_citas_findByMes(IN p_anio INT, IN p_mes INT)
BEGIN
    SELECT ci.id, ci.fecha, ci.hora_inicio, ci.duracion, ci.estado,
           c.nombre AS cliente_nombre, s.nombre AS servicio_nombre
    FROM citas ci
    LEFT  JOIN clientes      c ON c.id=ci.cliente_id
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    WHERE YEAR(ci.fecha)=p_anio AND MONTH(ci.fecha)=p_mes AND ci.estado!='Cancelada'
    ORDER BY ci.fecha ASC, ci.hora_inicio ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findById;
DELIMITER $$
CREATE PROCEDURE sp_citas_findById(IN p_id INT)
BEGIN
    SELECT ci.id, ci.cliente_id, ci.servicio_id, ci.user_id, ci.fecha, ci.hora_inicio,
           ci.duracion, ci.precio, ci.estado, ci.nota, ci.created_at, ci.updated_at,
           c.nombre AS cliente_nombre, c.telefono AS cliente_telefono,
           s.nombre AS servicio_nombre, s.duracion AS servicio_duracion, u.nombre AS empleado_nombre
    FROM citas ci
    LEFT  JOIN clientes      c ON c.id=ci.cliente_id
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    LEFT  JOIN users          u ON u.id=ci.user_id
    WHERE ci.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findByCliente;
DELIMITER $$
CREATE PROCEDURE sp_citas_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT ci.id, ci.fecha, ci.hora_inicio, ci.duracion, ci.precio, ci.estado, ci.nota, ci.created_at,
           s.nombre AS servicio_nombre, c.telefono AS cliente_telefono
    FROM citas ci
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    LEFT  JOIN clientes       c ON c.id=ci.cliente_id
    WHERE ci.cliente_id=p_cliente_id ORDER BY ci.fecha DESC, ci.hora_inicio DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_insert;
DELIMITER $$
CREATE PROCEDURE sp_citas_insert(
    IN p_cliente_id INT, IN p_servicio_id INT, IN p_user_id INT, IN p_fecha DATE,
    IN p_hora_inicio TIME, IN p_duracion INT, IN p_precio DECIMAL(10,2), IN p_nota TEXT
)
BEGIN
    INSERT INTO citas (cliente_id, servicio_id, user_id, fecha, hora_inicio, duracion, precio, nota)
    VALUES (p_cliente_id, p_servicio_id, p_user_id, p_fecha, p_hora_inicio, p_duracion, p_precio, p_nota);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_update;
DELIMITER $$
CREATE PROCEDURE sp_citas_update(
    IN p_id INT, IN p_cliente_id INT, IN p_servicio_id INT, IN p_user_id INT,
    IN p_fecha DATE, IN p_hora_inicio TIME, IN p_duracion INT, IN p_precio DECIMAL(10,2), IN p_nota TEXT
)
BEGIN
    UPDATE citas SET cliente_id=p_cliente_id, servicio_id=p_servicio_id, user_id=p_user_id,
        fecha=p_fecha, hora_inicio=p_hora_inicio, duracion=p_duracion, precio=p_precio, nota=p_nota
    WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_updateEstado;
DELIMITER $$
CREATE PROCEDURE sp_citas_updateEstado(IN p_id INT, IN p_estado VARCHAR(20))
BEGIN
    UPDATE citas SET estado=p_estado WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_delete;
DELIMITER $$
CREATE PROCEDURE sp_citas_delete(IN p_id INT)
BEGIN
    UPDATE citas SET estado='Cancelada' WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_verificarDisponibilidad;
DELIMITER $$
CREATE PROCEDURE sp_citas_verificarDisponibilidad(IN p_fecha DATE, IN p_hora_inicio TIME, IN p_duracion INT, IN p_exclude_id INT)
BEGIN
    DECLARE v_hora_fin TIME;
    SET v_hora_fin = ADDTIME(p_hora_inicio, SEC_TO_TIME(p_duracion * 60));
    SELECT COUNT(*) AS ocupadas FROM citas
    WHERE fecha=p_fecha AND estado NOT IN ('Cancelada') AND id!=COALESCE(p_exclude_id, 0)
      AND (hora_inicio < v_hora_fin AND ADDTIME(hora_inicio, SEC_TO_TIME(duracion*60)) > p_hora_inicio);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_countHoy;
DELIMITER $$
CREATE PROCEDURE sp_citas_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM citas WHERE fecha=CURDATE() AND estado!='Cancelada';
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_countPendientes;
DELIMITER $$
CREATE PROCEDURE sp_citas_countPendientes()
BEGIN
    SELECT COUNT(*) AS total FROM citas WHERE estado='Pendiente';
END$$
DELIMITER ;

-- ── FACTURACIÓN ───────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_facturacion_getConfig;
DELIMITER $$
CREATE PROCEDURE sp_facturacion_getConfig()
BEGIN
    SELECT id, nombre_fiscal, rtn, cai, rango_desde, rango_hasta, fecha_limite,
           establecimiento, punto_emision, direccion_fiscal, correlativo
    FROM facturacion_config WHERE id=1 LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_facturacion_updateConfig;
DELIMITER $$
CREATE PROCEDURE sp_facturacion_updateConfig(
    IN p_rtn VARCHAR(20), IN p_cai VARCHAR(50), IN p_rango_desde VARCHAR(20),
    IN p_rango_hasta VARCHAR(20), IN p_fecha_limite DATE, IN p_establecimiento VARCHAR(10),
    IN p_punto_emision VARCHAR(10), IN p_nombre_fiscal VARCHAR(150),
    IN p_direccion_fiscal TEXT, IN p_correlativo INT
)
BEGIN
    UPDATE facturacion_config SET rtn=p_rtn, cai=p_cai, rango_desde=p_rango_desde,
        rango_hasta=p_rango_hasta, fecha_limite=p_fecha_limite, establecimiento=p_establecimiento,
        punto_emision=p_punto_emision, nombre_fiscal=p_nombre_fiscal,
        direccion_fiscal=p_direccion_fiscal, correlativo=p_correlativo WHERE id=1;
END$$
DELIMITER ;

-- ── BANNERS ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_banners_findAll;
DELIMITER $$
CREATE PROCEDURE sp_banners_findAll()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo, created_at FROM banners ORDER BY orden ASC, id ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_findActivos;
DELIMITER $$
CREATE PROCEDURE sp_banners_findActivos()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo FROM banners WHERE activo=1 ORDER BY orden ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_findById;
DELIMITER $$
CREATE PROCEDURE sp_banners_findById(IN p_id INT)
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo FROM banners WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_insert;
DELIMITER $$
CREATE PROCEDURE sp_banners_insert(IN p_titulo VARCHAR(150), IN p_imagen_url VARCHAR(255), IN p_enlace VARCHAR(255), IN p_orden INT)
BEGIN
    INSERT INTO banners (titulo, imagen_url, enlace, orden) VALUES (p_titulo, p_imagen_url, p_enlace, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_update;
DELIMITER $$
CREATE PROCEDURE sp_banners_update(IN p_id INT, IN p_titulo VARCHAR(150), IN p_imagen_url VARCHAR(255), IN p_enlace VARCHAR(255), IN p_orden INT)
BEGIN
    UPDATE banners SET titulo=p_titulo, imagen_url=COALESCE(p_imagen_url, imagen_url), enlace=p_enlace, orden=p_orden WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_banners_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE banners SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_delete;
DELIMITER $$
CREATE PROCEDURE sp_banners_delete(IN p_id INT)
BEGIN
    DELETE FROM banners WHERE id=p_id;
END$$
DELIMITER ;

-- ── GALERÍA ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_galeria_findAll;
DELIMITER $$
CREATE PROCEDURE sp_galeria_findAll()
BEGIN
    SELECT id, imagen_url, descripcion, orden, activo, created_at FROM galeria_clientes ORDER BY orden ASC, id DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_findActivas;
DELIMITER $$
CREATE PROCEDURE sp_galeria_findActivas()
BEGIN
    SELECT id, imagen_url, descripcion, orden, activo FROM galeria_clientes WHERE activo=1 ORDER BY orden ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_findById;
DELIMITER $$
CREATE PROCEDURE sp_galeria_findById(IN p_id INT)
BEGIN
    SELECT id, imagen_url, descripcion, orden, activo FROM galeria_clientes WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_insert;
DELIMITER $$
CREATE PROCEDURE sp_galeria_insert(IN p_imagen_url VARCHAR(255), IN p_descripcion VARCHAR(255), IN p_orden INT)
BEGIN
    INSERT INTO galeria_clientes (imagen_url, descripcion, orden) VALUES (p_imagen_url, p_descripcion, p_orden);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_update;
DELIMITER $$
CREATE PROCEDURE sp_galeria_update(IN p_id INT, IN p_imagen_url VARCHAR(255), IN p_descripcion VARCHAR(255), IN p_orden INT)
BEGIN
    UPDATE galeria_clientes SET imagen_url=COALESCE(p_imagen_url, imagen_url), descripcion=p_descripcion, orden=p_orden WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_galeria_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE galeria_clientes SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_delete;
DELIMITER $$
CREATE PROCEDURE sp_galeria_delete(IN p_id INT)
BEGIN
    DELETE FROM galeria_clientes WHERE id=p_id;
END$$
DELIMITER ;

-- ── ZONAS DE ENVÍO ────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_zonas_findAll;
DELIMITER $$
CREATE PROCEDURE sp_zonas_findAll()
BEGIN
    SELECT id, nombre, costo, activo, created_at FROM zonas_envio ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_findActivas;
DELIMITER $$
CREATE PROCEDURE sp_zonas_findActivas()
BEGIN
    SELECT id, nombre, costo, activo FROM zonas_envio WHERE activo=1 ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_findById;
DELIMITER $$
CREATE PROCEDURE sp_zonas_findById(IN p_id INT)
BEGIN
    SELECT id, nombre, costo, activo FROM zonas_envio WHERE id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_insert;
DELIMITER $$
CREATE PROCEDURE sp_zonas_insert(IN p_nombre VARCHAR(100), IN p_costo DECIMAL(10,2))
BEGIN
    INSERT INTO zonas_envio (nombre, costo) VALUES (p_nombre, p_costo);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_update;
DELIMITER $$
CREATE PROCEDURE sp_zonas_update(IN p_id INT, IN p_nombre VARCHAR(100), IN p_costo DECIMAL(10,2))
BEGIN
    UPDATE zonas_envio SET nombre=p_nombre, costo=p_costo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_toggleActivo;
DELIMITER $$
CREATE PROCEDURE sp_zonas_toggleActivo(IN p_id INT, IN p_activo TINYINT)
BEGIN
    UPDATE zonas_envio SET activo=p_activo WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_zonas_delete;
DELIMITER $$
CREATE PROCEDURE sp_zonas_delete(IN p_id INT)
BEGIN
    DELETE FROM zonas_envio WHERE id=p_id;
END$$
DELIMITER ;

-- ── REPORTES ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reportes_ventasPorDia;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorDia()
BEGIN
    SELECT DATE(created_at) AS fecha, COUNT(*) AS total_ventas, SUM(total) AS total_monto
    FROM ventas WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY fecha ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMes;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorMes()
BEGIN
    SELECT DATE_FORMAT(created_at,'%Y-%m') AS mes, DATE_FORMAT(created_at,'%b %Y') AS mes_label,
           COUNT(*) AS total_ventas, SUM(total) AS total_monto
    FROM ventas WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY mes ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMetodo;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorMetodo()
BEGIN
    SELECT metodo_pago, COUNT(*) AS total_ventas, SUM(total) AS total_monto
    FROM ventas GROUP BY metodo_pago ORDER BY total_monto DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_topProductos;
DELIMITER $$
CREATE PROCEDURE sp_reportes_topProductos()
BEGIN
    SELECT vd.nombre_producto, SUM(vd.cantidad) AS total_vendido, SUM(vd.subtotal) AS total_monto
    FROM venta_detalle vd INNER JOIN ventas v ON v.id=vd.venta_id
    GROUP BY vd.nombre_producto ORDER BY total_vendido DESC LIMIT 10;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenVentas;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenVentas()
BEGIN
    SELECT COUNT(*) AS total_ventas, COALESCE(SUM(total),0) AS total_monto,
           COALESCE(AVG(total),0) AS promedio_venta,
           COALESCE(SUM(CASE WHEN DATE(created_at)=CURDATE() THEN total END),0) AS total_hoy,
           COALESCE(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) THEN total END),0) AS total_mes
    FROM ventas;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorEstado;
DELIMITER $$
CREATE PROCEDURE sp_reportes_pedidosPorEstado()
BEGIN
    SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado ORDER BY total DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorDia;
DELIMITER $$
CREATE PROCEDURE sp_reportes_pedidosPorDia()
BEGIN
    SELECT DATE(created_at) AS fecha, COUNT(*) AS total_pedidos, SUM(total) AS total_monto
    FROM pedidos WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY fecha ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenPedidos;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenPedidos()
BEGIN
    SELECT COUNT(*) AS total,
           SUM(CASE WHEN estado='Pendiente'      THEN 1 ELSE 0 END) AS pendientes,
           SUM(CASE WHEN estado='En preparacion' THEN 1 ELSE 0 END) AS preparacion,
           SUM(CASE WHEN estado='Listo'          THEN 1 ELSE 0 END) AS listos,
           SUM(CASE WHEN estado='En camino'      THEN 1 ELSE 0 END) AS en_camino,
           SUM(CASE WHEN estado='Entregado'      THEN 1 ELSE 0 END) AS entregados,
           SUM(CASE WHEN estado='Cancelado'      THEN 1 ELSE 0 END) AS cancelados
    FROM pedidos;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_stockBajo;
DELIMITER $$
CREATE PROCEDURE sp_reportes_stockBajo(IN p_limite INT)
BEGIN
    SELECT p.id, p.nombre, p.stock, c.nombre AS categoria_nombre, p.precio_base, p.image_url
    FROM productos p INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.activo=1 AND p.tiene_variantes=0 AND p.stock<=p_limite ORDER BY p.stock ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_variantesStockBajo;
DELIMITER $$
CREATE PROCEDURE sp_reportes_variantesStockBajo(IN p_limite INT)
BEGIN
    SELECT v.id, v.nombre AS variante_nombre, v.stock, p.nombre AS producto_nombre,
           COALESCE(v.precio, p.precio_base) AS precio
    FROM producto_variantes v INNER JOIN productos p ON p.id=v.producto_id
    WHERE v.activo=1 AND v.stock<=p_limite ORDER BY v.stock ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenInventario;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenInventario()
BEGIN
    SELECT COUNT(*) AS total_productos,
           SUM(CASE WHEN activo=1 THEN 1 ELSE 0 END) AS activos,
           SUM(CASE WHEN stock=0 AND tiene_variantes=0 THEN 1 ELSE 0 END) AS sin_stock,
           SUM(CASE WHEN stock<=5 AND tiene_variantes=0 THEN 1 ELSE 0 END) AS stock_bajo
    FROM productos;
END$$
DELIMITER ;
USE anamarcol;

DROP PROCEDURE IF EXISTS sp_categorias_findActivas;
DELIMITER $$
CREATE PROCEDURE sp_categorias_findActivas()
BEGIN
    SELECT id, nombre, descripcion, activo FROM categorias WHERE activo=1 ORDER BY nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_users_findByRol;
DELIMITER $$
CREATE PROCEDURE sp_users_findByRol(IN p_rol_id INT)
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.rol_id, u.activo, u.foto, u.telefono,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id WHERE u.rol_id=p_rol_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_combos_removeProducto;
DELIMITER $$
CREATE PROCEDURE sp_combos_removeProducto(IN p_id INT)
BEGIN
    DELETE FROM combo_productos WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_pedidos_countHoy;
DELIMITER $$
CREATE PROCEDURE sp_pedidos_countHoy()
BEGIN
    SELECT COUNT(*) AS total FROM pedidos WHERE DATE(created_at)=CURDATE();
END$$
DELIMITER ;

-- ============================================================
-- Notificaciones — Tabla y Stored Procedures
-- Ejecutar en phpMyAdmin sobre la BD anamarcol
-- ============================================================

USE anamarcol;

CREATE TABLE IF NOT EXISTS notificaciones (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    tipo       VARCHAR(20)   NOT NULL COMMENT 'cita, pedido, stock',
    titulo     VARCHAR(100)  NOT NULL,
    mensaje    VARCHAR(255)  NOT NULL,
    url        VARCHAR(255)  DEFAULT NULL,
    leida      TINYINT(1)    NOT NULL DEFAULT 0,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS sp_notificaciones_findAll;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_findAll()
BEGIN
    SELECT id, tipo, titulo, mensaje, url, leida, created_at
    FROM notificaciones ORDER BY created_at DESC LIMIT 30;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_notificaciones_countNoLeidas;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_countNoLeidas()
BEGIN
    SELECT COUNT(*) AS total FROM notificaciones WHERE leida=0;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_notificaciones_insert;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_insert(
    IN p_tipo    VARCHAR(20),
    IN p_titulo  VARCHAR(100),
    IN p_mensaje VARCHAR(255),
    IN p_url     VARCHAR(255)
)
BEGIN
    INSERT INTO notificaciones (tipo, titulo, mensaje, url)
    VALUES (p_tipo, p_titulo, p_mensaje, p_url);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_notificaciones_marcarLeida;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_marcarLeida(IN p_id INT)
BEGIN
    UPDATE notificaciones SET leida=1 WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_notificaciones_marcarTodasLeidas;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_marcarTodasLeidas()
BEGIN
    UPDATE notificaciones SET leida=1 WHERE leida=0;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_notificaciones_delete;
DELIMITER $$
CREATE PROCEDURE sp_notificaciones_delete(IN p_id INT)
BEGIN
    DELETE FROM notificaciones WHERE id=p_id;
END$$
DELIMITER ;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- Credenciales de Ana Marcol:
-- Email:    ana@anamarcol.com
-- Usuario:  anamarcol
-- Password: password
-- ⚠️  CAMBIAR LA CONTRASEÑA AL PRIMER INICIO DE SESIÓN
-- ============================================================