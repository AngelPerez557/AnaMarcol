# Sesión D — Instructivo de deploy

**Fecha:** 2026-05-14
**Cubre:** F-05, F-06, F-09, F-15, F-16, F-17, F-18, F-21
**Riesgo:** ALTO — toca login, logout y verificación de sesión
**Reversibilidad:** Total — `git revert` del commit

---

## Cambios aplicados (8 hallazgos cerrados)

| ID | Cambio | Archivo |
|----|--------|---------|
| **F-05** | `session_token` ahora se persiste dentro de `Auth::login()` | `Config/Core/Auth.php` |
| **F-06** | `Auth::check()` loguea errores en vez de fallar silencioso | `Config/Core/Auth.php` |
| **F-09** | Logout requiere CSRF token (admin y tienda) | `index.php`, `AuthController`, `TiendaController`, `menu.php`, `Template/Tienda/index.php` |
| **F-15** | `Auth::require()` llama `check()` antes (redirige al login en vez de 403) | `Config/Core/Auth.php` |
| **F-16** | `AuthController::login` ya no aplica `htmlspecialchars` al email | `Controllers/AuthController.php` |
| **F-17** | Lógica de auth centralizada — el Controller solo orquesta | `Config/Core/Auth.php` + `AuthController` |
| **F-18** | Login con CSRF token (previene Login CSRF) | `AuthController` + `Views/Auth/login.php` |
| **F-21** | Permisos refrescados con TTL 5 min (sin esperar logout) | `Config/Core/Auth.php` |

## Archivos modificados (6)

```
M  Config/Core/Auth.php           ← rewrite completo
M  Controllers/AuthController.php ← limpieza + CSRF + saneo
M  Controllers/TiendaController.php ← CSRF en logout
M  index.php                      ← se quita interceptación de logout
M  Views/Auth/login.php           ← Csrf::field() en form
M  Template/Default/menu.php      ← link logout con CSRF
M  Template/Tienda/index.php      ← link logout con CSRF
```

---

## Comportamientos nuevos

### F-09 — Logout protegido contra CSRF

**Antes:**
```html
<img src="http://18.218.192.129/Auth/logout"> <!-- desloguea automáticamente -->
```

**Ahora:**
- El logout solo se ejecuta si llega con CSRF token correcto
- Los links del menú admin y tienda ya envían el token en `?csrf=...`
- Un atacante sin acceso al token no puede forzar logout

### F-18 — Login con CSRF

- El form de login ahora incluye `<input type="hidden" name="csrf_token" value="...">`
- Si alguien envía un POST sin el token correcto → mensaje "Sesión inválida"
- Previene Login CSRF (atacante forzando víctima a loguearse en cuenta ajena)

### F-21 — Refresh de permisos automático

- Cuando admin agrega/quita permisos a un rol, los usuarios afectados los reciben **dentro de 5 minutos** sin tener que hacer logout
- Antes: cambios solo aplicaban al próximo login

### F-15 — UX mejorada en 403

**Antes:**
- Usuario sin sesión → click en `/Usuarios/eliminar` → 403 "Sin permiso"
- Confuso, parecía falta de permiso

**Ahora:**
- Usuario sin sesión → cualquier endpoint protegido → redirige a `/Auth/index`
- Solo se muestra 403 si el usuario está logueado pero le falta ese permiso

---

## Smoke tests críticos

### Test 1 — Login normal

```
1. Abrir http://18.218.192.129/Auth/index
2. Login con un user válido
3. Debe redirigir al Dashboard
4. F12 → Network → buscar el POST a /Auth/login
   → confirmar que el body incluye csrf_token=...
```

### Test 2 — Login CSRF rechazado

```
1. Abrir http://18.218.192.129/Auth/index
2. F12 → Console → ejecutar:
   document.querySelector('input[name="csrf_token"]').value = 'falso';
3. Llenar credenciales y submit
4. Debe redirigir a /Auth/index con error "Sesión inválida"
   (NO debe loguear)
```

### Test 3 — Logout normal

```
1. Login OK
2. Click en el botón "Cerrar sesión" del menú
3. Debe redirigir a /Auth/index
4. Estar deslogueado
```

### Test 4 — Logout CSRF rechazado

```
1. Login OK
2. En otra pestaña pegar:
   http://18.218.192.129/Auth/logout
   (sin ?csrf=...)
3. Debe redirigir a /Dashboard/index — NO debe cerrar sesión
4. Volver a la primera pestaña, recargar — debes seguir logueado
```

### Test 5 — Permisos refrescados (F-21)

```
1. Login con un usuario "Empleado"
2. En otra ventana/sesión admin: revocar el permiso "productos.ver" al rol
3. Esperar 5+ minutos
4. En la sesión del empleado, ir a /Productos/index
5. Debe ver 403 (antes lo dejaba pasar hasta el próximo logout)
```

### Test 6 — Otros endpoints siguen funcionando

| Endpoint | Esperado |
|----------|----------|
| `/Dashboard/index` | Carga |
| `/Reportes/ventas` | Carga |
| Crear/editar Producto | POST con CSRF pasa |
| Logout desde dropdown de tienda (`/Tienda/logout?csrf=...`) | Cierra sesión cliente |

---

## Rollback si algo va mal

```bash
git revert HEAD
git push
```

No requiere tocar BD (los SPs de Sesión A siguen siendo válidos).

### Si el error es "session_token incoherente"

Limpiá las sesiones de todos los usuarios — esto los desloguea pero no rompe nada:

```sql
UPDATE users SET session_token = NULL;
```

Después relogueá. La nueva sesión genera token correcto.

---

## Pendientes para la próxima

| Sesión | Cubre | Bloqueador |
|--------|-------|------------|
| **B** (opcional) | F-01, F-02, F-07, F-33 — `.env` | Requiere coordinar `.env` con tu compañero |
| **E** (última crítica) | F-08, F-23, F-32 — Rate limiter en BD | Requiere tabla nueva `rate_limits` |
| **C2** (cosmético) | F-30 — fix XSS de user content en ~30 Views | Riesgo bajo |
| **HTTPS** | F-34 | Comprar dominio + Let's Encrypt |

---

## Resumen de tokens

Sesión D completa cierra **8 de los 11 hallazgos críticos pendientes**. Después de validar este deploy, solo quedan:
- 4 medios (Sesión B)
- 3 altos (Sesión E)
- 1 externo (HTTPS)

> **Estado de la auditoría:** 24 de 39 hallazgos resueltos (62%). Críticos restantes: 0 de auth. 3 de rate limiting (Sesión E).
