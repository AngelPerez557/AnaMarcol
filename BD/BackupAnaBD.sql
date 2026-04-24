-- ============================================================
-- AnaMarcolMakeupStudios — BD Completa v2.0
-- Adaptada para MySQL 8 (ONLY_FULL_GROUP_BY compatible)
-- Desarrollado por DeskCod
-- ============================================================
-- INSTRUCCIONES:
-- 1. Abrir phpMyAdmin en el servidor
-- 2. Crear BD vacía llamada "anamarcol"
-- 3. Seleccionar la BD y ejecutar este script completo
-- ============================================================

CREATE DATABASE IF NOT EXISTS anamarcol
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE anamarcol;

-- Deshabilitar ONLY_FULL_GROUP_BY para compatibilidad MySQL 8
SET SESSION sql_mode = (SELECT REPLACE(@@SESSION.sql_mode, 'ONLY_FULL_GROUP_BY', ''));

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- SECCIÓN 1 — TABLAS
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
    CONSTRAINT fk_citas_cliente  FOREIGN KEY (cliente_id)  REFERENCES clientes       (id) ON DELETE SET NULL,
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

-- ── NOTIFICACIONES ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notificaciones (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    tipo       VARCHAR(20)   NOT NULL COMMENT 'cita, pedido, stock',
    titulo     VARCHAR(100)  NOT NULL,
    mensaje    VARCHAR(255)  NOT NULL,
    url        VARCHAR(255)  DEFAULT NULL,
    leida      TINYINT(1)    NOT NULL DEFAULT 0,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SECCIÓN 2 — DATOS INICIALES
-- ============================================================

-- ── ROL ADMIN ─────────────────────────────────────────────────
INSERT INTO roles (id, nombre, slug, descripcion) VALUES
(1, 'Administrador', 'admin', 'Acceso total al sistema');

-- ── PERMISOS ─────────────────────────────────────────────────
INSERT INTO permissions (nombre, slug, modulo) VALUES
('Ver categorías',        'categorias.ver',          'categorias'),
('Crear categorías',      'categorias.crear',         'categorias'),
('Editar categorías',     'categorias.editar',        'categorias'),
('Eliminar categorías',   'categorias.eliminar',      'categorias'),
('Ver productos',         'productos.ver',            'productos'),
('Crear productos',       'productos.crear',          'productos'),
('Editar productos',      'productos.editar',         'productos'),
('Eliminar productos',    'productos.eliminar',       'productos'),
('Ver combos',            'combos.ver',               'combos'),
('Crear combos',          'combos.crear',             'combos'),
('Editar combos',         'combos.editar',            'combos'),
('Eliminar combos',       'combos.eliminar',          'combos'),
('Ver ventas',            'ventas.ver',               'ventas'),
('Crear ventas',          'ventas.crear',             'ventas'),
('Eliminar ventas',       'ventas.eliminar',          'ventas'),
('Ver pedidos',           'pedidos.ver',              'pedidos'),
('Crear pedidos',         'pedidos.crear',            'pedidos'),
('Gestionar pedidos',     'pedidos.gestionar',        'pedidos'),
('Eliminar pedidos',      'pedidos.eliminar',         'pedidos'),
('Ver clientes',          'clientes.ver',             'clientes'),
('Crear clientes',        'clientes.crear',           'clientes'),
('Ver citas',             'citas.ver',                'citas'),
('Crear citas',           'citas.crear',              'citas'),
('Editar citas',          'citas.editar',             'citas'),
('Eliminar citas',        'citas.eliminar',           'citas'),
('Ver servicios',         'servicios.ver',            'citas'),
('Crear servicios',       'servicios.crear',          'citas'),
('Editar servicios',      'servicios.editar',         'citas'),
('Eliminar servicios',    'servicios.eliminar',       'citas'),
('Ver facturación',       'facturacion.ver',          'facturacion'),
('Crear facturas',        'facturacion.crear',        'facturacion'),
('Configurar facturación','facturacion.configurar',   'facturacion'),
('Ver reportes',          'reportes.ver',             'reportes'),
('Exportar reportes',     'reportes.exportar',        'reportes'),
('Ver usuarios',          'usuarios.ver',             'usuarios'),
('Crear usuarios',        'usuarios.crear',           'usuarios'),
('Editar usuarios',       'usuarios.editar',          'usuarios'),
('Eliminar usuarios',     'usuarios.eliminar',        'usuarios'),
('Ver roles',             'roles.ver',                'roles'),
('Crear roles',           'roles.crear',              'roles'),
('Editar roles',          'roles.editar',             'roles'),
('Eliminar roles',        'roles.eliminar',           'roles'),
('Ver tienda',            'tienda.ver',               'tienda'),
('Configurar tienda',     'tienda.configurar',        'tienda'),
('Ver banners',           'banners.ver',              'tienda'),
('Gestionar banners',     'banners.gestionar',        'tienda'),
('Ver galería',           'galeria.ver',              'tienda'),
('Gestionar galería',     'galeria.gestionar',        'tienda'),
('Ver zonas',             'zonas.ver',                'tienda'),
('Gestionar zonas',       'zonas.gestionar',          'tienda');

-- ── ASIGNAR TODOS LOS PERMISOS AL ROL ADMIN ──────────────────
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permissions;

-- ── CONFIG FACTURACIÓN (Datos de Ana Marcol) ──────────────────
INSERT INTO facturacion_config (
    id, nombre_fiscal, rtn, cai, rango_desde, rango_hasta,
    fecha_limite, establecimiento, punto_emision, direccion_fiscal, correlativo
) VALUES (
    1,
    'ANA MARCOL MAKEUP STUDIO',
    '16012001003960',
    '22E397-65F31F-2EE8E0-63BE03-09091A-42',
    '000-001-01-00003001',
    '000-001-01-00006000',
    '2025-09-24',
    '000',
    '001',
    'Barrio Abajo Avenida La Libertad, Tegucigalpa',
    5740
);

-- ── CONFIG CITAS ──────────────────────────────────────────────
INSERT INTO citas_config (
    id, horario_inicio, horario_fin,
    dias_laborales, duracion_default, capacidad_simultanea
) VALUES (
    1, '08:00:00', '18:00:00', '1,2,3,4,5,6', 60, 1
);

-- ============================================================
-- SECCIÓN 3 — USUARIO ADMINISTRADOR (Ana Marcol)
-- ============================================================
-- ⚠️  IMPORTANTE: Cambiar la contraseña al primer inicio
-- Credenciales por defecto:
--   Email:    ana@anamarcol.com
--   Usuario:  anamarcol
--   Password: password
-- tour_completado = 0 → verá el tour al primer ingreso
-- ============================================================

INSERT INTO users (
    id, nombre, username, email, password,
    rol_id, activo, tour_completado
) VALUES (
    1,
    'Ana Marcol',
    'anamarcol',
    'ana@anamarcol.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    1, 1, 0
);

-- ============================================================
-- SECCIÓN 4 — STORED PROCEDURES
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

DROP PROCEDURE IF EXISTS sp_permissions_insert;
DELIMITER $$
CREATE PROCEDURE sp_permissions_insert(
    IN p_nombre VARCHAR(100), IN p_slug VARCHAR(100),
    IN p_modulo VARCHAR(60), IN p_descripcion VARCHAR(255)
)
BEGIN
    INSERT INTO permissions (nombre, slug, modulo, descripcion)
    VALUES (p_nombre, p_slug, p_modulo, p_descripcion);
    SELECT LAST_INSERT_ID() AS id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_update;
DELIMITER $$
CREATE PROCEDURE sp_permissions_update(
    IN p_id INT, IN p_nombre VARCHAR(100), IN p_slug VARCHAR(100),
    IN p_modulo VARCHAR(60), IN p_descripcion VARCHAR(255)
)
BEGIN
    UPDATE permissions
    SET nombre=p_nombre, slug=p_slug, modulo=p_modulo, descripcion=p_descripcion
    WHERE id=p_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_permissions_delete;
DELIMITER $$
CREATE PROCEDURE sp_permissions_delete(IN p_id INT)
BEGIN
    DELETE FROM permissions WHERE id=p_id;
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

DROP PROCEDURE IF EXISTS sp_users_findByRol;
DELIMITER $$
CREATE PROCEDURE sp_users_findByRol(IN p_rol_id INT)
BEGIN
    SELECT u.id, u.nombre, u.username, u.email, u.rol_id, u.activo, u.foto, u.telefono,
           r.slug AS rol_slug, r.nombre AS rol_nombre
    FROM users u INNER JOIN roles r ON r.id=u.rol_id WHERE u.rol_id=p_rol_id;
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

DROP PROCEDURE IF EXISTS sp_clientes_update;
DELIMITER $$
CREATE PROCEDURE sp_clientes_update(
    IN p_id INT, IN p_nombre VARCHAR(120), IN p_email VARCHAR(120),
    IN p_telefono VARCHAR(20), IN p_direccion TEXT
)
BEGIN
    UPDATE clientes
    SET nombre=p_nombre, email=p_email, telefono=p_telefono, direccion=p_direccion
    WHERE id=p_id;
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

DROP PROCEDURE IF EXISTS sp_categorias_findActivas;
DELIMITER $$
CREATE PROCEDURE sp_categorias_findActivas()
BEGIN
    SELECT id, nombre, descripcion, activo FROM categorias WHERE activo=1 ORDER BY nombre ASC;
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

DROP PROCEDURE IF EXISTS sp_categorias_hasProductos;
DELIMITER $$
CREATE PROCEDURE sp_categorias_hasProductos(IN p_id INT)
BEGIN
    SELECT COUNT(*) AS total
    FROM productos
    WHERE categoria_id=p_id AND activo=1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_categorias_count;
DELIMITER $$
CREATE PROCEDURE sp_categorias_count()
BEGIN
    SELECT COUNT(*) AS total FROM categorias;
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

DROP PROCEDURE IF EXISTS sp_productos_findSimpleByBarras;
DELIMITER $$
CREATE PROCEDURE sp_productos_findSimpleByBarras(IN p_barras VARCHAR(100))
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.precio_base,
           p.stock, p.image_url, p.activo, c.nombre AS categoria_nombre
    FROM productos p
    INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.codigo_barras=p_barras AND p.activo=1 AND p.tiene_variantes=0
    LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_findByNombre;
DELIMITER $$
CREATE PROCEDURE sp_productos_findByNombre(IN p_nombre VARCHAR(150))
BEGIN
    SELECT p.id, p.categoria_id, p.nombre, p.descripcion, p.precio_base,
           p.tiene_variantes, p.stock, p.codigo_barras, p.image_url, p.activo,
           c.nombre AS categoria_nombre
    FROM productos p
    INNER JOIN categorias c ON c.id=p.categoria_id
    WHERE p.activo=1 AND p.nombre LIKE CONCAT('%', p_nombre, '%')
    ORDER BY p.nombre ASC
    LIMIT 20;
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

DROP PROCEDURE IF EXISTS sp_productos_countActivos;
DELIMITER $$
CREATE PROCEDURE sp_productos_countActivos()
BEGIN
    SELECT COUNT(*) AS total FROM productos WHERE activo=1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_productos_updateStock;
DELIMITER $$
CREATE PROCEDURE sp_productos_updateStock(IN p_id INT, IN p_cantidad INT)
BEGIN
    UPDATE productos
    SET stock = stock - p_cantidad
    WHERE id = p_id AND tiene_variantes = 0 AND activo = 1 AND stock >= p_cantidad;

    SELECT ROW_COUNT() AS afectado;
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

DROP PROCEDURE IF EXISTS sp_variantes_findByProducto;
DELIMITER $$
CREATE PROCEDURE sp_variantes_findByProducto(IN p_producto_id INT)
BEGIN
    SELECT id, producto_id, nombre, precio, stock, codigo_barras, image_url, activo, orden
    FROM producto_variantes
    WHERE producto_id=p_producto_id
    ORDER BY orden ASC, nombre ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_variantes_findById;
DELIMITER $$
CREATE PROCEDURE sp_variantes_findById(IN p_id INT)
BEGIN
    SELECT id, producto_id, nombre, precio, stock, codigo_barras, image_url, activo, orden
    FROM producto_variantes
    WHERE id=p_id
    LIMIT 1;
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

DROP PROCEDURE IF EXISTS sp_variantes_updateStock;
DELIMITER $$
CREATE PROCEDURE sp_variantes_updateStock(IN p_id INT, IN p_cantidad INT)
BEGIN
    UPDATE producto_variantes
    SET stock = stock - p_cantidad
    WHERE id = p_id AND activo = 1 AND stock >= p_cantidad;

    SELECT ROW_COUNT() AS afectado;
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

DROP PROCEDURE IF EXISTS sp_combos_removeProducto;
DELIMITER $$
CREATE PROCEDURE sp_combos_removeProducto(IN p_id INT)
BEGIN
    DELETE FROM combo_productos WHERE id=p_id;
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
    SELECT id, horario_inicio, horario_fin, dias_laborales, duracion_default, capacidad_simultanea
    FROM citas_config WHERE id=1 LIMIT 1;
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
    LEFT  JOIN clientes       c ON c.id=ci.cliente_id
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
    LEFT  JOIN clientes       c ON c.id=ci.cliente_id
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
    LEFT  JOIN clientes       c ON c.id=ci.cliente_id
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
           s.nombre AS servicio_nombre, s.duracion AS servicio_duracion,
           u.nombre AS empleado_nombre
    FROM citas ci
    LEFT  JOIN clientes       c ON c.id=ci.cliente_id
    INNER JOIN servicios_cita s ON s.id=ci.servicio_id
    LEFT  JOIN users          u ON u.id=ci.user_id
    WHERE ci.id=p_id LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_citas_findByCliente;
DELIMITER $$
CREATE PROCEDURE sp_citas_findByCliente(IN p_cliente_id INT)
BEGIN
    SELECT ci.id, ci.fecha, ci.hora_inicio, ci.duracion, ci.precio,
           ci.estado, ci.nota, ci.created_at,
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
    IN p_fecha DATE, IN p_hora_inicio TIME, IN p_duracion INT,
    IN p_precio DECIMAL(10,2), IN p_nota TEXT
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
CREATE PROCEDURE sp_citas_verificarDisponibilidad(
    IN p_fecha DATE, IN p_hora_inicio TIME, IN p_duracion INT, IN p_exclude_id INT
)
BEGIN
    DECLARE v_hora_fin TIME;
    SET v_hora_fin = ADDTIME(p_hora_inicio, SEC_TO_TIME(p_duracion * 60));
    SELECT COUNT(*) AS ocupadas FROM citas
    WHERE fecha=p_fecha AND estado NOT IN ('Cancelada')
      AND id != COALESCE(p_exclude_id, 0)
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
        rango_hasta=p_rango_hasta, fecha_limite=p_fecha_limite,
        establecimiento=p_establecimiento, punto_emision=p_punto_emision,
        nombre_fiscal=p_nombre_fiscal, direccion_fiscal=p_direccion_fiscal,
        correlativo=p_correlativo WHERE id=1;
END$$
DELIMITER ;

-- ── BANNERS ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_banners_findAll;
DELIMITER $$
CREATE PROCEDURE sp_banners_findAll()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo, created_at
    FROM banners ORDER BY orden ASC, id ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_banners_findActivos;
DELIMITER $$
CREATE PROCEDURE sp_banners_findActivos()
BEGIN
    SELECT id, titulo, imagen_url, enlace, orden, activo
    FROM banners WHERE activo=1 ORDER BY orden ASC;
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
    UPDATE banners SET titulo=p_titulo, imagen_url=COALESCE(p_imagen_url, imagen_url),
        enlace=p_enlace, orden=p_orden WHERE id=p_id;
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
    SELECT id, imagen_url, descripcion, orden, activo, created_at
    FROM galeria_clientes ORDER BY orden ASC, id DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_galeria_findActivas;
DELIMITER $$
CREATE PROCEDURE sp_galeria_findActivas()
BEGIN
    SELECT id, imagen_url, descripcion, orden, activo
    FROM galeria_clientes WHERE activo=1 ORDER BY orden ASC;
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
    UPDATE galeria_clientes SET imagen_url=COALESCE(p_imagen_url, imagen_url),
        descripcion=p_descripcion, orden=p_orden WHERE id=p_id;
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

-- ── NOTIFICACIONES ────────────────────────────────────────────
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
    IN p_tipo VARCHAR(20), IN p_titulo VARCHAR(100),
    IN p_mensaje VARCHAR(255), IN p_url VARCHAR(255)
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

-- ── REPORTES (COMPATIBLES CON MySQL 8 ONLY_FULL_GROUP_BY) ────
DROP PROCEDURE IF EXISTS sp_reportes_ventasPorDia;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorDia()
BEGIN
    SELECT
        DATE(created_at) AS fecha,
        COUNT(*)          AS total_ventas,
        SUM(total)        AS total_monto
    FROM ventas
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY fecha ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMes;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorMes()
BEGIN
    SELECT
        DATE_FORMAT(created_at, '%Y-%m')      AS mes,
        DATE_FORMAT(MIN(created_at), '%b %Y') AS mes_label,
        COUNT(*)                               AS total_ventas,
        SUM(total)                             AS total_monto
    FROM ventas
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY mes ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_ventasPorMetodo;
DELIMITER $$
CREATE PROCEDURE sp_reportes_ventasPorMetodo()
BEGIN
    SELECT
        metodo_pago,
        COUNT(*)   AS total_ventas,
        SUM(total) AS total_monto
    FROM ventas
    GROUP BY metodo_pago
    ORDER BY total_monto DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_topProductos;
DELIMITER $$
CREATE PROCEDURE sp_reportes_topProductos()
BEGIN
    SELECT
        vd.nombre_producto,
        SUM(vd.cantidad) AS total_vendido,
        SUM(vd.subtotal) AS total_monto
    FROM venta_detalle vd
    INNER JOIN ventas v ON v.id = vd.venta_id
    GROUP BY vd.nombre_producto
    ORDER BY total_vendido DESC
    LIMIT 10;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenVentas;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenVentas()
BEGIN
    SELECT
        COUNT(*) AS total_ventas,
        COALESCE(SUM(total), 0) AS total_monto,
        COALESCE(AVG(total), 0) AS promedio_venta,
        COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total ELSE 0 END), 0) AS total_hoy,
        COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) AS total_mes
    FROM ventas;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorEstado;
DELIMITER $$
CREATE PROCEDURE sp_reportes_pedidosPorEstado()
BEGIN
    SELECT estado, COUNT(*) AS total
    FROM pedidos
    GROUP BY estado
    ORDER BY total DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_pedidosPorDia;
DELIMITER $$
CREATE PROCEDURE sp_reportes_pedidosPorDia()
BEGIN
    SELECT
        DATE(created_at) AS fecha,
        COUNT(*)          AS total_pedidos,
        SUM(total)        AS total_monto
    FROM pedidos
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY fecha ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenPedidos;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenPedidos()
BEGIN
    SELECT
        COUNT(*) AS total,
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
    FROM productos p
    INNER JOIN categorias c ON c.id = p.categoria_id
    WHERE p.activo=1 AND p.tiene_variantes=0 AND p.stock <= p_limite
    ORDER BY p.stock ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_variantesStockBajo;
DELIMITER $$
CREATE PROCEDURE sp_reportes_variantesStockBajo(IN p_limite INT)
BEGIN
    SELECT v.id, v.nombre AS variante_nombre, v.stock,
           p.nombre AS producto_nombre,
           COALESCE(v.precio, p.precio_base) AS precio
    FROM producto_variantes v
    INNER JOIN productos p ON p.id = v.producto_id
    WHERE v.activo=1 AND v.stock <= p_limite
    ORDER BY v.stock ASC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_reportes_resumenInventario;
DELIMITER $$
CREATE PROCEDURE sp_reportes_resumenInventario()
BEGIN
    SELECT
        COUNT(*) AS total_productos,
        SUM(CASE WHEN activo=1 THEN 1 ELSE 0 END) AS activos,
        SUM(CASE WHEN stock=0 AND tiene_variantes=0 THEN 1 ELSE 0 END) AS sin_stock,
        SUM(CASE WHEN stock<=5 AND tiene_variantes=0 THEN 1 ELSE 0 END) AS stock_bajo
    FROM productos;
END$$
DELIMITER ;

-- ============================================================
-- SECCIÓN 5 — INSERT DE PRODUCTOS Y CATEGORÍAS
-- (Ejecutar DESPUÉS de importar la BD principal)
-- ============================================================

-- ============================================================
-- SECCIÓN 5 — INSERT DE PRODUCTOS Y CATEGORÍAS
-- (Ejecutar DESPUÉS de importar la BD principal)
-- ============================================================

-- ── CATEGORÍAS MIGRADAS ───────────────────────────────────────
INSERT INTO categorias (id, nombre, descripcion, activo) VALUES
(1, 'Cuidado de la piel', 'Centella-Cerave-The Ordinary-Eucerin', 1),
(2, 'Maquillaje',         'Loreal-Maybelline-Elf-Rare Beauty',    1),
(3, 'Perfumeria',         'Splash-Crema-Perfumes-Exfoliantes',    1),
(4, 'Carteras',           'Carteras-Monederos',                    1),
(5, 'Cabello',            'Firenze-Tahe-Joico',                    1),
(6, 'Productos para uñas','',                                      1)
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), activo=1;

-- ── PRODUCTOS MIGRADOS ────────────────────────────────────────
INSERT INTO productos (categoria_id, nombre, descripcion, precio_base, stock, image_url, activo, tiene_variantes) VALUES
(1, 'Ácido Hialuronico The Ordinary 30ml', 'Ideal para pieles secas, brinda hidratación y mejora la textura', 420, 0, 'prod_6983c93bbb5066.46181112.webp', 1, 0),
(2, 'Easy Bake Setting Spray 100 ml', 'Fijador de maquillaje de larga duración (16 horas) que difumina poros y controla brillos.', 1390, 0, 'prod_6984f24ed19203.26024632.webp', 1, 0),
(2, 'On Till Dawn Big & Bitty One Size', 'Spray fijador matificante hasta 16 horas a prueba de sudor, impermeable y transferencia.', 1650, 0, 'prod_6984f30b4e7573.89010907.webp', 1, 0),
(2, 'Make It Last Milani Original', 'Fijador 3-en-1 para mantener el maquillaje impecable hasta 16 o 24 horas.', 370, 0, 'prod_6984f93adc87c5.61325526.webp', 1, 0),
(3, 'GOOD GIRL Carolina Herrera', 'Fragancia audaz y sofisticada que celebra la dualidad de la mujer moderna.', 3080, 0, 'prod_6984f9e9a534e9.83950513.webp', 1, 0),
(2, 'Setting Mist Beau Visage', 'Spray fijador de maquillaje con acabado mate para look impecable todo el día.', 250, 0, 'prod_6984fa90da65f2.70130985.jpeg', 1, 0),
(3, 'Moschino Toy Boy', 'Aroma intenso y moderno, especiado y seductor, para una masculinidad audaz.', 2320, 0, 'prod_6984facd4df5c2.83822881.jpeg', 1, 0),
(3, 'Stronger with YOU intensely', 'Fragancia cálida y adictiva, con notas dulces y especiadas que expresan intensidad y pasión.', 4500, 0, 'prod_6984fb5183d539.35942210.webp', 1, 0),
(2, 'Ruby kisses Matte Setting Spray', 'Proporciona hidratación instantánea y revitaliza la piel con acabado brillante.', 280, 0, 'prod_6984fb62a75349.98767429.webp', 1, 0),
(3, 'COACH Platinum', 'Fragancia masculina elegante con notas amaderadas y especiadas.', 2680, 0, 'prod_6984fbaddca605.52399439.webp', 1, 0),
(3, 'Couture Couture by Juicy Couture', 'Fragancia femenina glamurosa con notas dulces y florales.', 1700, 0, 'prod_6984fc12b9bcb7.25936808.webp', 1, 0),
(1, 'Sellador Make It Last Matte', 'Prepara, fija y matifica el maquillaje hasta 16 horas sin brillo.', 370, 0, 'prod_6984fcb21e8511.41306791.webp', 1, 0),
(3, 'Cloud 2.0 Ariana Grande', 'Fragancia dulce y envolvente con notas cremosas y modernas.', 2900, 0, 'prod_6984fcf9577aa4.34428755.webp', 1, 0),
(2, 'Setting Spray Milani Dewy', 'Fijador 3 en 1 para hidratar, iluminar y sellar el maquillaje hasta 16 horas.', 370, 0, 'prod_6984fd56dc9d25.52700259.webp', 1, 0),
(3, 'ARI by Ariana Grande', 'Fragancia dulce y juguetona con notas frutales y florales.', 2600, 0, 'prod_6984fd870c5290.47725979.webp', 1, 0),
(2, 'Ruby kisses Setting Spray', 'Fijador ligero de secado rápido para base, sombras y polvos.', 260, 0, 'prod_6984fdbfccac42.44441874.webp', 1, 0),
(3, 'Sweet Like Candy Ariana Grande', 'Fragancia dulce y divertida con notas frutales y gourmand.', 2600, 0, 'prod_6984fdc7535c42.04549580.webp', 1, 0),
(3, 'Jimmy Choo', 'Fragancia femenina elegante con notas dulces y afrutadas.', 2650, 0, 'prod_6984fe268b0332.86405345.webp', 1, 0),
(3, 'So Scandal Jean Paul Gaultier', 'Fragancia femenina audaz con notas florales y almizcladas.', 3950, 0, 'prod_6984ffa31e53b7.94889840.webp', 1, 0),
(2, 'Glass Spray Maybelline', 'Spray para efecto piel de cristal, brillante y luminoso.', 390, 0, 'prod_6984fff78528d6.41699545.jpeg', 1, 0),
(3, 'Versace Yellow Diamond', 'Fragancia femenina brillante con notas cítricas y florales.', 2980, 0, 'prod_69850043578157.74885376.webp', 1, 0),
(3, 'Gucci Flora', 'Fragancia femenina floral con notas de gardenia, fresca y sofisticada.', 3700, 0, 'prod_6985065d26c849.51769002.webp', 1, 0),
(3, 'Versace Bright Crystal', 'Fragancia femenina fresca y floral con toque frutal.', 3100, 0, 'prod_698506cf64d148.95696145.webp', 1, 0),
(3, 'Valentino Donna', 'Fragancia femenina elegante con notas florales y amaderadas.', 3300, 0, 'prod_698507118962d0.78365447.webp', 1, 0),
(3, 'N 5 CHANEL', 'Fragancia femenina icónica y atemporal con notas florales y aldehídicas.', 4900, 0, 'prod_698507925ddd96.96885827.jpeg', 1, 0),
(3, 'Libre YSL', 'Fragancia femenina audaz con notas florales y amaderadas.', 4700, 0, 'prod_6985081784db17.54292157.jpeg', 1, 0),
(3, '212 VIP ROSÉ Carolina Herrera', 'Fragancia femenina elegante y festiva con notas frutales y florales.', 3900, 0, 'prod_6985090f6df317.12987130.webp', 1, 0),
(3, 'VERY Good Girl', 'Fragancia femenina fresca y floral con toque moderno.', 4700, 0, 'prod_69850c39c84a14.74913323.jpeg', 1, 0),
(3, 'Devotion Dolce & Gabbana', 'Fragancia femenina floral y sensual con notas dulces y elegantes.', 3450, 0, 'prod_69850d4430c523.71780001.jpeg', 1, 0),
(3, 'COACH Floral Blush', 'Fragancia femenina fresca y floral con toque frutal.', 2400, 0, 'prod_69850da44bb922.89488272.webp', 1, 0),
(3, 'COACH', 'Fragancia femenina moderna con notas florales y amaderadas.', 2650, 0, 'prod_69850e19704924.90565068.jpeg', 1, 0),
(3, 'Miss Dior Absolutely Blooming', 'Fragancia floral-frutal con frambuesa, rosa y almizcle blanco.', 4690, 0, 'prod_69850f7509c412.34303959.webp', 1, 0),
(3, 'PRADA Paradoxe', 'Fragancia femenina moderna con notas de peonía, vainilla y maderas blancas.', 4300, 0, 'prod_69850fb3bfb1a5.26547190.jpeg', 1, 0),
(3, 'Bombshell Victoria\'s Secret', 'Fragancia femenina vibrante y sensual con notas frutales y florales.', 1780, 0, 'prod_69850ffd0c92f8.29303065.jpeg', 1, 0),
(3, 'YARA Tous', 'Fragancia tropical y vibrante con mango, coco y maracuyá.', 1600, 0, 'prod_6985107e129335.72018513.webp', 1, 0),
(3, 'YARA Candy', 'Fragancia dulce con notas frutales, gardenia y vainilla.', 1700, 0, 'prod_6985117f5ede57.90974277.webp', 1, 0),
(3, 'Set de Mini perfumes Burberry', 'Colección con versiones en miniatura de fragancias icónicas femeninas.', 2300, 0, 'prod_69851216a2dcc1.06606406.webp', 1, 0),
(3, 'Euphoria Calvin Klein', 'Fragancia femenina intensa y seductora con notas frutales y florales.', 1950, 0, 'prod_69851279e89ef0.13465070.webp', 1, 0),
(3, 'La vie est bella Lancome', 'Fragancia dulce con notas de iris, vainilla y praline.', 4300, 0, 'prod_698512c0762374.45254779.webp', 1, 0),
(3, 'La vie est belle Intensément Lancome', 'Fragancia intensa y profunda con notas dulces y florales.', 4300, 0, 'prod_6985131787fa58.50783566.jpeg', 1, 0),
(2, 'Power Grip Dewy Setting Spray', 'Sellador hidratante que fija el maquillaje y aporta luminosidad natural.', 450, 0, 'prod_698513972484b1.77756811.webp', 1, 0),
(3, 'Set Club de Nuit Armaf', 'Colección de fragancias masculinas intensas y sofisticadas.', 2800, 0, 'prod_6985139bbed993.09821871.webp', 1, 0),
(3, 'Set de Mini perfumes Yara', 'Colección femenina con fragancias dulces y vibrantes en tamaño mini.', 2380, 0, 'prod_698513d9e1cdd1.31536610.webp', 1, 0),
(2, 'Ultra Matte Moira Setting Spray', 'Sella el maquillaje y controla el exceso de brillo con acabado mate.', 380, 0, 'prod_69851406827d75.89629573.jpeg', 1, 0),
(2, 'Oil Control Setting Spray Moira', 'Fija el maquillaje y reduce el brillo con acabado mate ligero.', 380, 0, 'prod_6985146488af18.85968792.webp', 1, 0),
(3, 'Tease Créme Cloud VS', 'Fragancia cremosa y dulce con notas suaves y envolventes.', 580, 0, 'prod_69851467ab5661.60655060.jpeg', 1, 0),
(3, 'Tease Créme Cloud VS Splash', 'Fragancia fresca y cremosa con notas suaves y ligeras.', 580, 0, 'prod_698514fd763933.25923200.webp', 1, 0),
(2, 'Stay All Night Elf Setting Spray', 'Fija el maquillaje hasta por 16 horas sin retoques.', 380, 0, 'prod_6985150bb13a43.09056181.webp', 1, 0),
(3, 'Tease Sugar Fleur Crema VS', 'Fragancia dulce y floral con toque cremoso, romántica y suave.', 580, 0, 'prod_69851586354dc7.33354304.jpeg', 1, 0),
(2, 'Dewy Coconut Setting Spray', 'Fijador luminoso y natural que hidrata y acondiciona la piel.', 380, 0, 'prod_6985158e1de066.02998808.webp', 1, 0),
(2, 'Microdot Setting Spray', 'Fija el maquillaje con acabado mate tipo aerógrafo sin sensación pesada.', 490, 0, 'prod_698515e3d85db4.17147774.webp', 1, 0),
(2, 'Poreless Face Primer Elf', 'Minimiza la apariencia de poros dilatados, líneas finas y arrugas.', 380, 0, 'prod_698516228d2401.26444746.jpeg', 1, 0),
(3, 'Tease sugar Fleur Splash VS', 'Fragancia dulce y floral con toque ligero y fresco.', 580, 0, 'prod_6985165c8b2a66.34177410.webp', 1, 0),
(2, 'The Porefessional Primer 22 ml', 'Bálsamo primer que minimiza poros abiertos y líneas finas al instante.', 1350, 0, 'prod_698516879f86e9.60441449.jpeg', 1, 0),
(3, 'Crema Bombshell VS', 'Crema corporal femenina con aroma dulce y floral inspirado en Bombshell.', 580, 0, 'prod_698516d9a600f8.68913347.jpeg', 1, 0),
(2, 'Beauty Creations Poreless Face Primer', 'Minimiza poros dilatados y matifica la piel antes del maquillaje.', 260, 0, 'prod_698516dc208345.33204276.webp', 1, 0),
(2, 'Nyx Shine Killer Primer', 'Prebase matificante para pieles mixtas a grasas que controla el brillo.', 350, 0, 'prod_6985172d39aad8.11181676.jpeg', 1, 0),
(3, 'Crema Bombshell intense VS', 'Crema corporal con aroma profundo y seductor inspirado en Bombshell Intense.', 580, 0, 'prod_69851740835b34.65966088.webp', 1, 0),
(3, 'Splash Bombshell Soirée VS', 'Fragancia ligera y vibrante con notas frutales y florales.', 580, 0, 'prod_698517905c9b40.92387009.webp', 1, 0),
(2, 'Photofocus Matte Primer', 'Prepara la piel para el maquillaje creando una superficie suave y mate.', 280, 0, 'prod_69851792be5c28.99632812.webp', 1, 0),
(2, 'No pore zone Milani', 'Minimiza la apariencia de los poros con extractos de lirio y bambú.', 490, 0, 'prod_698517cff40de5.42577218.webp', 1, 0),
(3, 'Crema Bombshell Soirée VS', 'Crema corporal con aroma frutal y floral inspirado en Bombshell Soirée.', 580, 0, 'prod_698517f320d272.97960345.jpeg', 1, 0),
(2, 'Primer Loreal Prime Lab Matte', 'Matifica la piel y prolonga el maquillaje hasta 24 horas.', 430, 0, 'prod_6985188caa7308.18467373.png', 1, 0),
(2, 'Pore Minimizer Primer Loreal', 'Reduce la apariencia de poros, controla el brillo y matifica la piel.', 350, 0, 'prod_6985192586fcb0.11622274.jpeg', 1, 0),
(2, 'Primer Power Grip Elf', 'Prebase en gel hidratante que fija el maquillaje todo el día.', 390, 0, 'prod_69851b08a07998.45713958.webp', 1, 0),
(2, 'Primer Power Grip Niacinamida', 'Prebase en gel con 4% niacinamida que fija el maquillaje y unifica el tono.', 390, 0, 'prod_69851d7f33a769.45128381.jpeg', 1, 0),
(3, 'Splash star smoked amber VS', 'Fragancia corporal con notas cálidas de ámbar ahumado y toque dulce.', 480, 0, 'prod_698a3bfb7dbe93.67132364.jpeg', 1, 0),
(3, 'Splash Rich Caramel Vanilla VS', 'Splash corporal con notas cremosas de caramelo y vainilla.', 480, 0, 'prod_698a3cd44c05e9.27710868.jpeg', 1, 0),
(3, 'Splash Shimmering Shores VS', 'Splash corporal con aroma tropical de coco cremoso y notas marinas.', 480, 0, 'prod_698a3d4e2db7d7.92310530.webp', 1, 0),
(3, 'Splash Festive Fizz VS', 'Splash corporal chispeante con notas frutales y toque burbujeante.', 480, 0, 'prod_698a3db9778199.96706145.jpeg', 1, 0),
(3, 'Splash Cherry BonBon Bliss VS', 'Splash dulce con notas de cereza jugosa y caramelo.', 480, 0, 'prod_698a3dfd90e416.74206535.webp', 1, 0),
(2, 'Complete pore vanishing primer Moira', 'Prebase cremosa que disminuye visiblemente los poros y líneas finas.', 380, 0, 'prod_698a3edb2f9ae7.70727287.jpeg', 1, 0),
(3, 'Splash Sensuous cashmere rose VS', 'Splash elegante con notas de rosa suave y cashmere cremoso.', 480, 0, 'prod_698a3f1702fbf2.13962549.jpeg', 1, 0),
(3, 'Splash Midnight Bloom VS', 'Splash floral y misterioso con notas de flores nocturnas.', 480, 0, 'prod_698a3f5f2ef0c8.60957594.webp', 1, 0),
(3, 'Splash Bare Vanilla brúlée', 'Splash gourmand con notas de vainilla cremosa y azúcar caramelizada.', 480, 0, 'prod_698a3fbbbb7490.58474502.jpeg', 1, 0),
(2, 'Complete mattifying primer Moira', 'Controla la grasa, difumina poros y prolonga el maquillaje.', 380, 0, 'prod_698a4008eb05c3.82576848.webp', 1, 0),
(3, 'Splash Puré Seduction Brúlée VS', 'Splash seductor con notas de frutas jugosas y azúcar caramelizada.', 480, 0, 'prod_698a4009be4486.62361960.jpeg', 1, 0),
(3, 'Splash Chrome Peony VS', 'Splash floral y moderno con notas de peonía fresca.', 480, 0, 'prod_698a405d451381.51351006.jpeg', 1, 0),
(3, 'Splash Velvet Petals VS', 'Splash delicado y femenino con notas florales suaves y aterciopeladas.', 480, 0, 'prod_698a40983e40e1.83082914.jpeg', 1, 0),
(2, 'Complete smoothing primer Moira', 'Suaviza la textura, difumina poros y crea un lienzo sedoso.', 380, 0, 'prod_698a40f8301636.90895154.webp', 1, 0),
(3, 'Set Velvet Petals VS', 'Crema corporal y splash con aroma floral suave y aterciopelado.', 960, 0, 'prod_698a415f648893.75260069.jpeg', 1, 0),
(3, 'Scrub Raspberry Fizz Tree Hut', 'Exfoliante corporal con aroma a frambuesa dulce que renueva la piel.', 450, 0, 'prod_698a41dc49d234.12536633.jpeg', 1, 0),
(3, 'Scrub Moroccan Rose Tree Hut', 'Exfoliante corporal con rosa marroquí que deja la piel suave e hidratada.', 450, 0, 'prod_698a4219162b23.24632206.png', 1, 0),
(2, 'Ruby kisses blur & Matte Primer', 'Prepara la piel difuminando imperfecciones y poros.', 280, 0, 'prod_698a42da1f0838.32316647.webp', 1, 0),
(2, 'Hydrating grip primer Hydro boost neutrogena', 'Hidrata intensamente con ácido hialurónico y fija el maquillaje.', 350, 0, 'prod_698a4421143fb3.47590082.webp', 1, 0),
(2, 'Ruby kisses poreless face primer', 'Minimiza la apariencia de líneas finas y poros grandes.', 260, 0, 'prod_698a45782f2636.49585480.jpeg', 1, 0),
(2, 'Ruby kisses blemish control face primer', 'Prepara la piel y controla imperfecciones y el brillo facial.', 240, 0, 'prod_698a4798ea0546.87667077.jpeg', 1, 0),
(3, 'Scrub Coconut Lime Tree Hut', 'Exfoliante corporal con coco cremoso y lima fresca.', 450, 0, 'prod_698a4b63874df0.44530448.webp', 1, 0),
(3, 'Scrub Cotton Candy Tree Hut', 'Exfoliante suave con aroma a algodón de azúcar que renueva la piel.', 450, 0, 'prod_698a4bec6dfd05.39899210.webp', 1, 0),
(3, 'Scrub Pink Hibiscus Tree Hut', 'Exfoliante con partículas suaves y aroma floral tropical.', 450, 0, 'prod_698a4c38a95751.58103762.jpeg', 1, 0),
(3, 'Set Bare Vanilla Brulée VS', 'Vainilla y caramelo que envuelve tu piel en un aroma irresistible.', 960, 0, 'prod_6998a3f5678de7.09745350.jpeg', 1, 0),
(3, 'Set Velvet Petals brúlée VS', 'Flores aterciopeladas con toques dulces y cálidos.', 960, 0, 'prod_6998a416c5ace7.64623526.png', 1, 0),
(3, 'Set Pure Seduction brúlée VS', 'Aroma seductor y jugoso con notas frutales y dulces.', 960, 0, 'prod_6998a447ec17c6.55534216.webp', 1, 0),
(3, 'Set Naughty Spice VS', 'Fragancia audaz y seductora con notas cálidas y especiadas.', 980, 0, 'prod_6998a4ed4f62c9.78702032.webp', 1, 0),
(3, 'Set Sizzling Vanilla VS', 'Fragancia cálida con vainilla suave y un toque picante.', 960, 0, 'prod_6998ba97dc2046.56055307.jpeg', 1, 0),
(3, 'Set Island Rush VS', 'Aroma fresco y tropical que mezcla frutas exóticas y brisas marinas.', 960, 0, 'prod_6998bb37988035.78031411.jpeg', 1, 0),
(3, 'Set Neon Tropic VS', 'Fragancia vibrante y cítrica con frutas tropicales.', 960, 0, 'prod_6998bb849d9c69.22206791.png', 1, 0),
(3, 'Set Gilded Vanilla VS', 'Fragancia elegante con notas ricas de vainilla y matices gourmand.', 960, 0, 'prod_6998bbf0bd3d78.78255577.webp', 1, 0),
(3, 'Set Pure Seduction VS', 'Fragancia seductora con notas florales y afrutadas.', 960, 0, 'prod_6998bc47a03ec2.84589267.webp', 1, 0),
(3, 'Set Vibrant Breeze VS', 'Fragancia fresca y energizante con toques cítricos y brisa marina.', 960, 0, 'prod_6998bca26b9039.60765946.jpeg', 1, 0),
(3, 'Set Platinum Berries VS', 'Fragancia frutal y vibrante con notas intensas de bayas mixtas.', 960, 0, 'prod_6998bce7118b39.84597386.webp', 1, 0),
(3, 'Set Love Spell Candied VS', 'Fragancia dulce y encantadora con notas jugosas y caramelizadas.', 960, 0, 'prod_6998bd3665bd93.62355621.jpeg', 1, 0),
(3, 'Set Wild Neroli VS', 'Aroma fresco y floral con notas vibrantes de néroli silvestre.', 960, 0, 'prod_6998bd9d09b7d1.01024015.jpeg', 1, 0),
(3, 'Set Coconut Passion VS', 'Aroma tropical y cremoso con notas de coco y matices exóticos.', 960, 0, 'prod_6998bdd6daeb26.33221835.webp', 1, 0),
(3, 'Set Sensuous Cashmere Rose VS', 'Aroma sofisticado con notas suaves de rosa y cashmere.', 960, 0, 'prod_6998be317b5864.40629075.jpeg', 1, 0),
(3, 'Set Whispering Waves VS', 'Aroma fresco y evocador con notas marinas y brisa suave.', 960, 0, 'prod_6998be9f5a6705.12688362.jpeg', 1, 0),
(3, 'Set Chrome Peony VS', 'Fragancia moderna y floral con toques metálicos y peonía delicada.', 960, 0, 'prod_6998bee63c7ee9.12216724.webp', 1, 0),
(3, 'Crema Bare Vanilla Splash VS', 'Crema corporal con vainilla fresca que hidrata y perfuma la piel.', 480, 0, 'prod_6998bf247433a5.58336278.webp', 1, 0),
(2, 'Blush liquido Elf', 'Blush liquido Elf', 380, 0, 'prod_69e7b4f8a96330.05928538.webp', 1, 0),
(2, 'Blush Elf Halo Glow', 'Blush liquido Elf Halo glow', 450, 0, 'prod_69e7b5e9804cd0.49639062.webp', 1, 0),
(2, 'Blush liquido Pink Up', 'Blush liquido Pink Up', 270, 0, 'prod_69e7b78c6bad82.96951422.webp', 1, 0),
(2, 'Blush liquido Amor Us', 'Blush liquido Amor Us acabado Matte', 170, 0, 'prod_69e7b821adab42.81629474.webp', 1, 0),
(2, 'Blush liquido Beau visage', 'Blush liquido beau visage acabado matte', 180, 0, 'prod_69e7b8aea76d42.09709174.jpeg', 1, 0),
(2, 'Blush en crema Elf', 'Blush en crema Elf', 340, 0, 'prod_69e7b9607cd847.45580602.jpeg', 1, 0),
(2, 'Blush Pixi en barra', 'Blush en barra acabado luminoso y natural', 470, 0, 'prod_69e7bb4d4d5e63.48530085.jpeg', 1, 0),
(2, 'Rubor liquido Cloud soul Dapop', 'Rubor liquido acabado matte', 150, 0, 'prod_69e7bc4cf34862.24370337.webp', 1, 0),
(2, 'Rubor en crema Italia Deluxe', 'Blush en crema acabado matte', 150, 0, 'prod_69e7bcb41ab5b2.56913928.jpeg', 1, 0),
(2, 'Rubor en crema Ultramo', 'Rubor en crema', 140, 0, 'prod_69e7bd825bf404.51117822.webp', 1, 0),
(2, 'Correcto age Rewind Maybelline', 'Corrector líquido de cobertura media', 350, 0, 'prod_69e7be49e5fe15.14105680.webp', 1, 0),
(2, 'Corrector Lavish Creamy Moira', 'Corrector de alta cobertura cremosa para ojeras e imperfecciones.', 230, 0, 'prod_69e7bf1ecc7f96.92947569.webp', 1, 0),
(2, 'Corrector Milani', 'Cobertura completa con Ácido Hialurónico y Vitamina E de larga duración.', 350, 0, 'prod_69e7bfe57c6036.53313976.jpeg', 1, 0),
(2, 'Corrector beauty creations', 'Cobertura media a completa, fórmula de larga duración sin agrietarse.', 260, 0, 'prod_69e7c068748558.37713045.webp', 1, 0),
(3, 'Truly Glass Skin', 'Suero post depilación que calma, hidrata y reduce irritación y vellos encarnados.', 1690, 0, 'prod_69e7c601144ef8.88535346.webp', 1, 0),
(3, 'Truly Coco Cloud', 'Suero post depilación con extracto de coco que hidrata y suaviza la piel.', 1690, 0, 'prod_69e7c69bed1018.27950109.webp', 1, 0),
(3, 'Truly Glased Donut', 'Suero post depilación con acabado brillante y luminoso tipo piel glaseada.', 1690, 0, 'prod_69e7c6faa2f948.03094009.webp', 1, 0),
(3, 'Truly Unicorn Fruit', 'Suero post depilación que deja la piel luminosa tipo glass skin.', 1690, 0, 'prod_69e7c74b1762e8.14677128.webp', 1, 0),
(3, 'Truly Soft Serve', 'Suero post depilación con sensación fresca y piel suave.', 1690, 0, 'prod_69e7c791a360a9.01129889.webp', 1, 0),
(3, 'Truly Jelly Booster', 'Gel hidratante ligero que refresca la piel después de la depilación.', 1690, 0, 'prod_69e7c85e4e3e58.28478048.webp', 1, 0),
(3, 'Truly Cooka Colada', 'Manteca de afeitado para zona íntima que protege y previene irritación.', 800, 0, 'prod_69e7c8e013fb81.00271612.png', 1, 0),
(3, 'Truly Soft Serve Butter', 'Manteca de rasurado cremosa que facilita el deslizamiento y evita irritación.', 1320, 0, 'prod_69e7c939097243.64254743.jpeg', 1, 0),
(3, 'Set Truly The Viral Edit', 'Rutina completa post-afeitado para piel suave, uniforme y brillante.', 3600, 0, 'prod_69e7ca0a0ab700.02024352.webp', 1, 0),
(3, 'Set Jimmy Choo', 'Set de lujo Jimmy Choo con fragancia intensa y notas florales y vainilla.', 4500, 0, 'prod_69e7ca7eed6c92.49922406.webp', 1, 0),
(3, 'Gel de baño Tree Hut Tangerine', 'Limpiador espumoso con manteca de karité y aroma cítrico a mandarina.', 450, 0, 'prod_69e7daeaddbc41.30428697.jpeg', 1, 0),
(3, 'Gel de baño Tree Hut Moroccan Rose', 'Gel de baño espumoso con manteca de karité y aroma a rosa marroquí.', 450, 0, 'prod_69e7db8b2cd062.62649377.webp', 1, 0),
(2, 'Corrector Maybelline Super Stay', 'Larga duración, alta cobertura y textura ultra ligera hasta 30H.', 350, 0, 'prod_69e7f189d67264.97993910.webp', 1, 0),
(2, 'Corrector Elf', 'Corrector líquido de larga duración con cobertura total que hidrata.', 350, 0, 'prod_69e7f21173adc6.47384968.webp', 1, 0),
(2, 'CC Cream Elf', 'Base correctora de cobertura media a completa con protección SPF 30.', 350, 0, 'prod_69e7f390f370f7.44313749.jpeg', 1, 0),
(2, 'Base Beauty Creations Flawless Stay', 'Cobertura media a total con acabado natural para todo tipo de piel.', 290, 0, 'prod_69e7f457dfb483.32891200.webp', 1, 0),
(2, 'Base Beauty Creations Matte', 'Base ligera, resistente al agua, libre de aceites, cobertura 24 horas.', 390, 0, 'prod_69e7f5202453b5.59599803.webp', 1, 0),
(2, 'Base Wet N Wild', 'Base natural con cubrimiento y acabado mate.', 285, 0, 'prod_69e7f6110da282.23553681.webp', 1, 0),
(2, 'Base Eraser Maybelline', 'Con bayas de goji hidrata mientras cubre líneas finas y arrugas.', 450, 0, 'prod_69e7f74e60d567.68956032.jpeg', 1, 0),
(2, 'Base truly Matte LA Colors', 'Base de alta pigmentación con acabado Matte y larga duración.', 220, 0, 'prod_69e7f7ed013ed9.49938722.jpeg', 1, 0),
(2, 'Base Maybelline Súper Stay LUMI Matte', 'Base líquida que combina acabado mate con luminosidad natural.', 490, 0, 'prod_69e7f90d70d2b8.65517489.webp', 1, 0);
-- ============================================================
-- VERIFICACIÓN FINAL
-- ============================================================
SELECT 'TABLAS'     AS tipo, COUNT(*) AS total FROM information_schema.TABLES  WHERE TABLE_SCHEMA='anamarcol' AND TABLE_TYPE='BASE TABLE'
UNION ALL
SELECT 'PROCEDURES' AS tipo, COUNT(*) AS total FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='anamarcol' AND ROUTINE_TYPE='PROCEDURE'
UNION ALL
SELECT 'PRODUCTOS'  AS tipo, COUNT(*) AS total FROM productos
UNION ALL
SELECT 'CATEGORIAS' AS tipo, COUNT(*) AS total FROM categorias
UNION ALL
SELECT 'USUARIOS'   AS tipo, COUNT(*) AS total FROM users;

-- ============================================================
-- ⚠️  CREDENCIALES DE ANA MARCOL
-- Email:    ana@anamarcol.com
-- Usuario:  anamarcol
-- Password: password
-- CAMBIAR AL PRIMER INICIO DE SESIÓN
-- ============================================================
-- ℹ️  TOUR GUIADO
-- tour_completado = 0 → Ana y empleados nuevos verán el tour
-- al primer ingreso al sistema
-- El sistema lo marca como completado automáticamente
-- ============================================================