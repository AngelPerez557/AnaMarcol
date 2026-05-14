-- ────────────────────────────────────────────────────────────────
-- SESIÓN A — Auditoría de seguridad AnaMarcol
-- Fecha: 2026-05-14
--
-- Este script crea los stored procedures nuevos requeridos por
-- los cambios de la Sesión A. Ejecutar UNA SOLA VEZ en el servidor
-- de BD (anamarcol) ANTES de hacer commit del código PHP.
--
-- Orden de ejecución:
--   1. Ejecutar este .sql en MySQL/MariaDB
--   2. Verificar que los SPs existan (SHOW PROCEDURE STATUS WHERE Db='anamarcol')
--   3. Hacer commit y deploy del código PHP
-- ────────────────────────────────────────────────────────────────

USE anamarcol;

-- ────────────────────────────────────────────────────────────────
-- F-19 — sp_users_updateSessionToken
-- Reemplaza el UPDATE directo que vivía en AuthController.php
-- Mueve la lógica de BD al SP (Modelo, no Controller).
-- ────────────────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_users_updateSessionToken;
DELIMITER $$
CREATE PROCEDURE sp_users_updateSessionToken(
    IN p_id            INT,
    IN p_session_token VARCHAR(255)
)
BEGIN
    UPDATE users
       SET session_token = p_session_token
     WHERE id = p_id;
END$$
DELIMITER ;

-- ── Verificación ────────────────────────────────────────────────
-- SHOW PROCEDURE STATUS WHERE Db='anamarcol' AND Name LIKE 'sp_users_updateSessionToken';
--
-- Test:
-- CALL sp_users_updateSessionToken(1, 'test_token_abc123');
-- SELECT id, session_token FROM users WHERE id = 1;
-- ────────────────────────────────────────────────────────────────
