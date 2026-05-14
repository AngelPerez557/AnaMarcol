# Sesión A — Instructivo de deploy

**Fecha:** 2026-05-14
**Branch:** `security-fixes`
**Riesgo:** Bajo (refactor + defensa en profundidad, sin cambios de comportamiento visible)
**Reversibilidad:** Total — `git revert` del merge commit

---

## Hallazgos cubiertos en esta sesión

| ID | Severidad | Descripción |
|----|-----------|-------------|
| **F-03** | 🔴 CRÍTICO | `JRequest::sanitize()` aplicaba `htmlspecialchars+strip_tags` a todo input (anti-patrón). Eliminado. |
| **F-04** | 🔴 CRÍTICO | `JRouter` instanciaba clases por nombre de URL sin whitelist. Ahora valida contra whitelist auto-generada de `/Controllers/`. |
| **F-14** | 🟡 MEDIO | `AutoLoad` validaba `file_exists` sin regex previa. Ahora valida `$className` contra `/^[A-Za-z0-9_\\\/]+$/` y bloquea `..`. |
| **F-19** | 🔴 CRÍTICO | `AuthController::login` ejecutaba `UPDATE users SET session_token = ?` directo. Movido a `UserModel::updateSessionToken()` + nuevo SP `sp_users_updateSessionToken`. |
| **F-20** | 🔴 CRÍTICO | `ReportesController` tenía SQL directo + helper privado. Creado `ReporteModel` con 11 métodos. Controller solo orquesta. |
| **F-24** | 🟢 BAJO | `UserModel::usernameExists` y `emailExists` ahora `?int $excludeId = null`. |
| **F-26** | 🟠 ALTO | Eliminado helper privado `callSP()` duplicado en `ReportesController`. |
| **F-35** | 🟡 MEDIO | `JRequest::parseParams` (dead code, nadie consume `$request->getParam`). Eliminado. |

---

## Archivos modificados (6)

```
M  Config/JRequest.php             ← rewrite (90 → 89 líneas)
M  Config/JRouter.php              ← rewrite (126 → 154 líneas)
M  Config/AutoLoad.php             ← patch (regex + ..)
M  Models/UserModel.php            ← +updateSessionToken, F-24 cosmético
M  Controllers/AuthController.php  ← quita SQL directo
M  Controllers/ReportesController.php  ← rewrite (83 → 67 líneas)
```

## Archivos nuevos (2)

```
A  Models/ReporteModel.php                ← 11 métodos de reportes
A  BD/sql/sesion-A-2026-05-14.sql         ← SP nuevo sp_users_updateSessionToken
```

---

## Orden de deploy

⚠️ **Importante:** El SP nuevo se ejecuta ANTES del código. Si despliegan el código primero, el login fallará silenciosamente (el `UPDATE` no encontrará el SP) — la sesión se inicia pero el token no se persiste.

### Paso 1 — Backup de BD (recomendado)

En el servidor donde corre MySQL/MariaDB:

```bash
mysqldump -u root -p \
  --single-transaction --routines --triggers --events \
  anamarcol > /backups/anamarcol_pre_sesionA_$(date +%Y%m%d_%H%M).sql

# Verifica que el archivo no esté vacío
ls -lh /backups/anamarcol_pre_sesionA_*.sql
```

### Paso 2 — Ejecutar el SP nuevo

```bash
mysql -u root -p anamarcol < BD/sql/sesion-A-2026-05-14.sql
```

Verificar que el SP exista:

```sql
SHOW PROCEDURE STATUS WHERE Db='anamarcol' AND Name = 'sp_users_updateSessionToken';
```

Debe retornar 1 fila.

### Paso 3 — Deploy del código

```bash
# En el servidor:
cd /var/www/anamarcol
git fetch origin
git checkout security-fixes
git pull origin security-fixes

# Si usan symlink/release, ajustar según su workflow
```

### Paso 4 — Smoke tests

Probar en orden, marcando ✅ o ❌:

| # | Test | Esperado |
|---|------|----------|
| 1 | Abrir `http://18.218.192.129/Auth/index` | Carga login normal |
| 2 | Hacer login con credenciales válidas | Redirige a Dashboard |
| 3 | Cerrar sesión | Redirige a Auth/index |
| 4 | Login con password incorrecto | Mensaje "Correo o contraseña incorrectos" |
| 5 | Login con email inexistente | Mismo mensaje (no revela existencia) |
| 6 | Después de login, abrir `/Dashboard/index` | Dashboard carga |
| 7 | Abrir `/Usuarios/index` (si tienes permiso) | Lista usuarios |
| 8 | Abrir `/Reportes/ventas` (con permiso `reportes.ver`) | Reporte carga sin error 500 |
| 9 | Abrir `/Reportes/pedidos` | Carga |
| 10 | Abrir `/Reportes/inventario` | Carga |
| 11 | Acceder a una URL inexistente: `/NoExisteController/index` | 404 (no 500 ni white screen) |
| 12 | Intentar guardar un Producto/Cliente/Categoria | Guarda normal |
| 13 | Verificar en BD: `SELECT id, session_token FROM users WHERE id = (tu_user_id);` | Token presente y diferente al previo |

Si **alguno falla**, NO hacer rollback automático todavía. Hay que diagnosticar primero — el sistema antes tenía la misma funcionalidad rota silenciosamente (F-05). Compartir el error para `[diagnose]`.

### Paso 5 — Si todo OK, merge a main

```bash
git checkout main
git merge --no-ff security-fixes -m "Sesión A — F-03, F-04, F-14, F-19, F-20, F-24, F-26, F-35"
git push origin main
```

### Paso 6 — Rollback de emergencia (si necesario)

```bash
# Código:
git revert <hash_merge_commit>
git push origin main

# BD (el SP nuevo no rompe nada si queda, pero si quieren limpiar):
mysql -u root -p anamarcol -e "DROP PROCEDURE IF EXISTS sp_users_updateSessionToken;"
```

---

## Notas técnicas

### Cambio de comportamiento más importante — F-03

Antes, **cualquier input** que pasara por `JRequest::getParam()` venía sanitizado con `htmlspecialchars+strip_tags`. Esto era dead code (nadie lo usaba — los Controllers leen `$_POST` directo), pero la documentación del archivo daba la impresión opuesta.

Ahora `JRequest` solo expone segmentos de URL. **Los Controllers siguen leyendo `$_POST`/`$_GET` igual que antes** — esto no cambia. Pero el contrato del archivo ahora es claro: NO sanea, la responsabilidad de escapar está en la View al imprimir (`htmlspecialchars` antes de `<?= $var ?>`).

### F-04 — Whitelist auto

El JRouter ahora escanea `/Controllers/*Controller.php` la primera vez por request. Si agregan controllers nuevos, **se reconocen automáticamente** al hacer deploy — no hay que tocar nada.

Lo único que esto bloquea: que alguien suba un archivo malicioso por otro vector (ej: upload mal validado) y lo termine en una carpeta registrada por AutoLoad. Antes, ese archivo se podía ejecutar vía URL. Ahora solo se ejecutan los archivos que efectivamente están en `/Controllers/`.

### F-19 — Lo que cambió en login

El comportamiento es **idéntico** al usuario final. La diferencia es interna:
- Antes: `$db->prepare("UPDATE users SET session_token = ?...")` directo en el controller
- Ahora: `$userModel->updateSessionToken(...)` → `CALL sp_users_updateSessionToken(...)`

El campo `session_token` en la tabla `users` ya existe; no hay migración de schema.

### F-20 — Lo que cambió en reportes

El comportamiento es **idéntico**. La diferencia es que ahora hay un `ReporteModel.php` que centraliza los 11 SPs de reportes en lugar de tenerlos como SQL semihardcoded en el Controller.

---

## Pendiente — Próximas sesiones

| Sesión | Cubre | Bloquea? |
|--------|-------|----------|
| **B** — Config & secrets | F-01, F-02, F-07, F-33 | Requiere coordinar `.env` con DevOps |
| **C** — Headers, CSP, CSRF helper, XSS | F-10, F-11, F-12, F-22, F-29, F-30, F-31, F-34 | Independiente |
| **D** — Auth & sesiones | F-05, F-06, F-15, F-16, F-17, F-18, F-21 | Requiere backup obligatorio |
| **E** — RateLimiter en BD | F-08, F-23, F-32 | Requiere tabla nueva |
