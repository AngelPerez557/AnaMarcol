# Sesión E — Instructivo de deploy

**Fecha:** 2026-05-14
**Cubre:** F-08, F-23, F-32 (RateLimiter en BD)
**Riesgo:** Medio — toca el flujo de login, requiere migración SQL
**Reversibilidad:** Total — `git revert` + `DROP TABLE rate_limits`

---

## Cambios aplicados

| ID | Cambio | Archivo |
|----|--------|---------|
| **F-08** | Rate limit movido de `$_SESSION` a tabla `rate_limits` | `Config/Core/RateLimiter.php`, `Models/RateLimitModel.php` (NUEVO) |
| **F-23** | Manejo de error explícito si BD cae (fail open + log) | `Config/Core/RateLimiter.php` |
| **F-32** | Sin `md5()` — la IP se usa directo como PRIMARY KEY | `Config/Core/RateLimiter.php` |
| | Tabla `rate_limits` + 3 SPs nuevos | `BD/sql/sesion-E-2026-05-14.sql` (NUEVO) |

## Archivos modificados/nuevos

```
M  Config/Core/RateLimiter.php           ← rewrite
A  Models/RateLimitModel.php             ← NUEVO
A  BD/sql/sesion-E-2026-05-14.sql        ← NUEVO
```

---

## ORDEN DE DEPLOY (crítico)

⚠️ La tabla `rate_limits` **debe existir antes** del deploy del código. Si no, el primer intento de login crashea con "Table doesn't exist".

### Paso 1 — Ejecutar SQL en BD del servidor

Desde la terminal del servidor:

```bash
mysql -u zonamarcol_user -pAaPR2005_ anamarcol < /var/www/AnaMarcol/BD/sql/sesion-E-2026-05-14.sql
```

O desde Workbench/SQLyog conectado al servidor:
- File → Open → `sesion-E-2026-05-14.sql`
- Execute All (Ctrl+Shift+Enter)

### Paso 2 — Verificar tabla y SPs

```sql
USE anamarcol;

-- Tabla creada
SHOW TABLES LIKE 'rate_limits';

-- 3 SPs nuevos
SHOW PROCEDURE STATUS
 WHERE Db='anamarcol' AND Name LIKE 'sp_rate_limits_%';
-- Debe mostrar: sp_rate_limits_check, sp_rate_limits_register_fallo, sp_rate_limits_limpiar

-- Test funcional
CALL sp_rate_limits_register_fallo('1.2.3.4', 5, 15);
CALL sp_rate_limits_check('1.2.3.4');
-- Debe mostrar: intentos=1, bloqueado=0, minutos_restantes=0

CALL sp_rate_limits_limpiar('1.2.3.4');
```

### Paso 3 — git pull del código

```bash
cd /var/www/AnaMarcol
git fetch origin
git pull origin security-fixes
```

---

## Smoke tests

| # | Test | Esperado |
|---|------|----------|
| 1 | Login con credenciales válidas | OK, redirige a Dashboard |
| 2 | Login con password incorrecto 4 veces | OK, mensaje de error normal |
| 3 | Login con password incorrecto 5ª vez | Bloqueado por 15 min |
| 4 | Verificar tabla: `SELECT * FROM rate_limits;` | 1 fila con la IP, `bloqueado_hasta` con hora futura |
| 5 | Intentar login durante el bloqueo | Mensaje "Por seguridad el acceso está bloqueado. Intenta en N minuto(s)" |
| 6 | Login exitoso después de pasar 15 min | Tabla `rate_limits` debe vaciarse para esa IP |

### Test de la mejora real (F-08)

```
Antes:
1. Hago 4 intentos fallidos
2. Borro cookies del navegador
3. Hago 4 intentos más
4. Sigo sin bloqueo ✗ (vulnerable a brute force)

Ahora:
1. Hago 4 intentos fallidos
2. Borro cookies del navegador
3. Intento 1 vez más
4. Bloqueado ✓ (la BD recuerda mi IP)
```

---

## Política de "fail open" (F-23)

Si la BD se cae **durante** un login:
- `check()` retorna `true` → permite intentar
- Error queda en `error_log` del servidor
- El usuario legítimo NO queda bloqueado por un problema de infraestructura

Trade-off: en ese escenario, el sistema temporalmente queda sin protección contra brute force. La alternativa (bloquear todo) tendría peor UX en producción.

---

## Rollback si algo va mal

### Código

```bash
git revert HEAD
git push
```

### BD (opcional — la tabla huérfana no rompe nada)

```sql
DROP TABLE IF EXISTS rate_limits;
DROP PROCEDURE IF EXISTS sp_rate_limits_check;
DROP PROCEDURE IF EXISTS sp_rate_limits_register_fallo;
DROP PROCEDURE IF EXISTS sp_rate_limits_limpiar;
```

---

## Estado final de la auditoría

```
✅ Resueltos: 27 de 39 hallazgos (69%)
   Sesión A: F-03/04/14/19/20/24/26/35  (8)
   Sesión C: F-10/11/12/22/29/30/31/39  (8)
   Sesión D: F-05/06/09/15/16/17/18/21  (8)
   Sesión E: F-08/23/32                 (3)

⏳ Pendientes: 12
   Sesión B (env):    F-01/02/07/33      (4 medios — `.env`)
   Sesión C2:         F-30 user content  (~30 Views, bajo)
   Externos:          F-13/25/34/36/37/38 (proceso/infra)
```

**Toda la auditoría crítica de código está resuelta.** Lo que queda es:
- Sesión B: medios — requiere coordinar `.env` con DevOps
- F-34: HTTPS — necesita dominio + Let's Encrypt
- F-38: versionado de migraciones SQL (proceso)
