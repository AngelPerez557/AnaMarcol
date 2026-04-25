-- ============================================
-- SCRIPT: Crear SP sp_users_activarTour
-- BASE: anamarcol
-- DESCRIPCIÓN: Marca tour_completado = 0 para reiniciar tour
-- ============================================

USE anamarcol;

-- Eliminar SP anterior si existe
DROP PROCEDURE IF EXISTS sp_users_activarTour;

-- Cambiar delimitador para poder usar ; dentro del SP
DELIMITER $$

-- Crear nuevo SP
CREATE PROCEDURE sp_users_activarTour(IN p_id INT)
BEGIN
    -- Validar que el ID sea válido
    IF p_id > 0 THEN
        -- Actualizar tour_completado a 0 (falso)
        UPDATE users 
        SET tour_completado = 0, 
            updated_at = NOW()
        WHERE id = p_id;
        
        -- Confirmar cambios
        SELECT ROW_COUNT() AS filas_actualizadas;
    ELSE
        -- Si ID inválido, retornar error
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'ID de usuario inválido';
    END IF;
END$$

-- Volver a delimitador normal
DELIMITER ;

-- ============================================
-- VERIFICACIÓN
-- ============================================

-- Ver que el SP se creó correctamente
SHOW PROCEDURE STATUS WHERE Db='anamarcol' AND Name='sp_users_activarTour';

-- Ver que existe en INFORMATION_SCHEMA
SELECT ROUTINE_NAME, ROUTINE_TYPE 
FROM INFORMATION_SCHEMA.ROUTINES 
WHERE ROUTINE_SCHEMA='anamarcol' 
AND ROUTINE_NAME='sp_users_activarTour';

-- Test: Llamar el SP para usuario con ID=1
-- (Descomenta si quieres probar)
-- CALL sp_users_activarTour(1);
-- SELECT id, nombre, tour_completado FROM users WHERE id=1;
