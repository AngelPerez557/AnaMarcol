-- ════════════════════════════════════════════════════════════════
-- Sesión E — RateLimiter en BD
-- Fecha: 2026-05-14
-- Cubre: F-08 (rate limit en $_SESSION), F-23 (manejo error), F-32 (md5)
--
-- IMPORTANTE: Ejecutar este SQL ANTES del deploy del código PHP.
-- Sin la tabla rate_limits, el sistema cae al primer intento de login.
-- ════════════════════════════════════════════════════════════════

USE anamarcol;

-- ────────────────────────────────────────────────────────────────
-- TABLA rate_limits
-- Persiste los intentos de login fallidos por IP a través de
-- reinicios de navegador (el atacante no puede resetear borrando
-- cookies — lo que sí podía con la versión vieja en $_SESSION).
-- ────────────────────────────────────────────────────────────────
DROP TABLE IF EXISTS rate_limits;
CREATE TABLE rate_limits (
    ip               VARCHAR(45)  NOT NULL PRIMARY KEY,  -- IPv4 hasta 15, IPv6 hasta 45
    intentos         INT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_intento   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bloqueado_hasta  DATETIME     NULL,
    INDEX idx_bloqueado_hasta (bloqueado_hasta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────────
-- SP sp_rate_limits_check
-- Retorna estado actual del bloqueo para una IP.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_rate_limits_check;
DELIMITER $$
CREATE PROCEDURE sp_rate_limits_check(IN p_ip VARCHAR(45))
BEGIN
    SELECT
        intentos,
        bloqueado_hasta,
        CASE
            WHEN bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW()
                THEN GREATEST(0, TIMESTAMPDIFF(MINUTE, NOW(), bloqueado_hasta))
            ELSE 0
        END AS minutos_restantes,
        CASE
            WHEN bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW() THEN 1
            ELSE 0
        END AS bloqueado
    FROM rate_limits
    WHERE ip = p_ip
    LIMIT 1;
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- SP sp_rate_limits_register_fallo
-- Incrementa el contador. Si supera el umbral, bloquea por N minutos.
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_rate_limits_register_fallo;
DELIMITER $$
CREATE PROCEDURE sp_rate_limits_register_fallo(
    IN p_ip            VARCHAR(45),
    IN p_max_intentos  INT,
    IN p_bloqueo_min   INT
)
BEGIN
    DECLARE v_existe        INT DEFAULT 0;
    DECLARE v_nuevos        INT DEFAULT 1;

    SELECT COUNT(*), COALESCE(intentos, 0) + 1
      INTO v_existe, v_nuevos
      FROM rate_limits
     WHERE ip = p_ip;

    IF v_existe = 0 THEN
        INSERT INTO rate_limits (ip, intentos, ultimo_intento, bloqueado_hasta)
        VALUES (p_ip, 1, NOW(), NULL);
    ELSE
        UPDATE rate_limits
           SET intentos = v_nuevos,
               ultimo_intento = NOW(),
               bloqueado_hasta = CASE
                   WHEN v_nuevos >= p_max_intentos
                       THEN DATE_ADD(NOW(), INTERVAL p_bloqueo_min MINUTE)
                   ELSE bloqueado_hasta
               END
         WHERE ip = p_ip;
    END IF;
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- SP sp_rate_limits_limpiar
-- Limpia los intentos para una IP (al login exitoso).
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_rate_limits_limpiar;
DELIMITER $$
CREATE PROCEDURE sp_rate_limits_limpiar(IN p_ip VARCHAR(45))
BEGIN
    DELETE FROM rate_limits WHERE ip = p_ip;
END$$
DELIMITER ;

-- ────────────────────────────────────────────────────────────────
-- VERIFICACIÓN — después de ejecutar:
--
-- SHOW TABLES LIKE 'rate_limits';
-- SHOW PROCEDURE STATUS WHERE Db='anamarcol' AND Name LIKE 'sp_rate_limits_%';
--
-- Test funcional:
-- CALL sp_rate_limits_register_fallo('127.0.0.1', 5, 15);
-- CALL sp_rate_limits_register_fallo('127.0.0.1', 5, 15);
-- CALL sp_rate_limits_check('127.0.0.1');     -- Debe mostrar intentos=2, bloqueado=0
-- CALL sp_rate_limits_limpiar('127.0.0.1');
-- ════════════════════════════════════════════════════════════════
