# Sesión C — Instructivo de deploy

**Fecha:** 2026-05-14
**Cubre:** F-10, F-11, F-12, F-22, F-29, F-30, F-31, F-34, F-39
**Riesgo:** Bajo-medio — Sin BD, sin auth crítico, sin credenciales
**Reversibilidad:** Total — `git revert` del merge commit

---

## Cambios aplicados

| Archivo | Acción |
|---------|--------|
| `Config/Core/Csrf.php` | NUEVO — Helper CSRF con `hash_equals()` |
| `Config/Core/WebViewDetector.php` | NUEVO — Detecta Instagram/Facebook/TikTok WebView |
| `Template/Tienda/index.php` | Banner "Abrir en navegador" condicional |
| `index.php` | CSP, HSTS condicional, Permissions-Policy, quitado X-XSS-Protection |
| 19 archivos `Controllers/*.php` | Reemplazo de `!==` por `Csrf::validate()` |

## Hallazgos cubiertos

| ID | Estado |
|----|--------|
| **F-10** | ✅ Content-Security-Policy implementado |
| **F-11** | ✅ X-XSS-Protection removido |
| **F-12** | ✅ HSTS condicional (solo bajo HTTPS) |
| **F-22** | ✅ `hash_equals()` en lugar de `!==` |
| **F-29** | ✅ 59 puntos refactorizados a `Csrf::validate()` |
| **F-30** | 📋 Audit terminado — reporte abajo (sin modificar Views todavía) |
| **F-31** | 📋 CSP no permite inline event handlers nuevos |
| **F-34** | 📋 Pendiente HTTPS (acción tuya cuando pasen a dominio) |
| **F-39** | ✅ Banner Instagram WebView (NUEVO hallazgo cubierto) |

---

## Lo importante para tu compañero (DevOps)

### Deploy: pull simple, SIN tocar BD

```bash
cd /var/www/anamarcol
git fetch origin
git checkout security-fixes
git pull origin security-fixes
```

**No requiere:** backup BD, ejecutar SQL, reiniciar nada. Solo pull.

### Verificación post-deploy

Abrir las 3 URLs y verificar:

```
1. http://18.218.192.129/Auth/index           → Login admin carga
2. http://18.218.192.129/Tienda               → Tienda pública carga
3. http://18.218.192.129/Tienda/catalogo      → Productos visibles
```

Si todo carga, verificar **headers** con curl:

```bash
curl -I http://18.218.192.129/Tienda 2>&1 | grep -iE 'content-security|x-frame|referrer|permissions'
```

Debe mostrar:
```
Content-Security-Policy: default-src 'self'; script-src...
X-Frame-Options: SAMEORIGIN
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=()...
```

---

## Smoke tests funcionales (10 minutos)

| # | Test | OK / Falla |
|---|------|------------|
| 1 | Login admin con user real | Redirige a Dashboard |
| 2 | Dashboard carga (todos los widgets) | |
| 3 | Crear/editar un producto | Guarda |
| 4 | Eliminar algo (categoría sin productos) | El POST con CSRF pasa, elimina OK |
| 5 | Abrir tienda pública | Carga inicio |
| 6 | Catálogo de productos | Todos visibles |
| 7 | Agregar producto al carrito | Toast aparece |
| 8 | Logout admin | Vuelve a Auth/index |
| 9 | Login cliente en `/Tienda/login` | Redirige a tienda |
| 10 | Checkout completo (hasta confirmar pedido) | Pedido se crea |

### Tests específicos de Sesión C

| # | Test | Cómo |
|---|------|------|
| 11 | CSRF helper funciona | Abrir DevTools → editar el campo `csrf_token` de un form → enviar → debe dar 403 |
| 12 | CSP no rompe Views | F12 → Console → no debe haber `Refused to load...` o `Refused to execute inline...` |
| 13 | Banner Instagram aparece | Abrir el link `http://18.218.192.129/Tienda` desde Instagram in-app browser → debe verse el banner amarillo arriba |
| 14 | Banner se cierra y recuerda | Tocar la X del banner → recargar página → banner debe seguir cerrado |
| 15 | Botón "Abrir" del banner | En Android: abre Chrome. En iOS: muestra las instrucciones |

### Si CSP rompe algo (lo más probable que falle)

Síntoma: una página tiene scripts inline o estilos inline custom que no están en la lista permitida.

**Diagnóstico:**
1. F12 → Console
2. Buscar mensajes tipo:
   ```
   Refused to load the script 'https://otro-dominio.com/...' because it violates...
   ```
3. **Si es un dominio externo no listado** (ej. Google Fonts, MapBox, etc.):
   - Editar `index.php`
   - Agregar el dominio al `$csp` correspondiente
   - Commit + deploy

**Mientras tanto, rollback inmediato:**
```bash
git revert HEAD
git push
```

---

## F-30 — Reporte de outputs sin escape (sin modificar Views)

Inventario completo en el codebase actual:

```
Outputs <?= $var ?> totales:    426
htmlspecialchars() calls:        380
Outputs sospechosos (sin escape): 231
```

**Top 10 archivos con outputs sin escape:**

| Cantidad | Archivo |
|----------|---------|
| 18 | `Views/Productos/index.php` |
| 13 | `Views/Caja/index.php` |
| 13 | `Views/Soporte/index.php` |
| 11 | `Views/Dashboard/index.php` |
| 11 | `Views/Soporte/Ver.php` |
| 9 | `Views/Tienda/Citas.php` |
| 8 | `Views/Citas/index.php` |
| 8 | `Views/Tienda/Catalogo.php` |
| 7 | `Views/Pedidos/Detalle.php` |
| 7 | `Views/Tienda/Inicio.php` |

**Por qué no se corrigen automáticamente:** muchos de esos 231 outputs son **falsos positivos** (IDs numéricos, valores ya casteados con `(int)`, helpers como `calcDesc()`). Hacer fix masivo automático corre riesgo de:
- Doble encoding en lugares que ya escapan
- Romper formato visual donde el HTML es intencional (ej. tags como `<i>` armados dinámicamente)

**Estrategia recomendada:** revisión manual archivo por archivo, priorizando los que reciben input de usuario (Soporte, formularios de Productos/Citas). Lo dejamos para una mini-sesión "Sesión C2" cuando los críticos estén estables.

### Riesgo real de los 231

| Tipo | Cantidad estimada | Riesgo XSS |
|------|-------------------|------------|
| IDs numéricos (`$producto['id']`) | ~120 | Nulo — son enteros |
| Atributos `style="color: <?= $c['color'] ?>"` con data de BD | ~40 | Bajo — admin Honest UI |
| Texto de usuarios/clientes (`$ticket['titulo']`) | ~30 | **MEDIO** — XSS si admin escribe `<script>` en un ticket |
| Helpers que ya escapan internamente | ~25 | Nulo |
| Constantes/calculados | ~16 | Nulo |

El 13% medio (los 30 textos de usuario) son los que sí queremos fixear. CSP los mitiga parcialmente (segunda capa). El fix completo queda para Sesión C2.

---

## F-39 — Banner Instagram WebView (NUEVO)

### Cómo se ve

Al abrir la URL desde Instagram (o Facebook, TikTok, etc.), aparece arriba un banner amarillo:

```
ℹ️ Para una mejor experiencia (especialmente al iniciar sesión o pagar),
   abrí esta tienda en tu navegador. Toca los tres puntos (•••)
   arriba a la derecha y elegí «Abrir en Safari».              [Abrir]  [×]
```

### Detección que hace

Detecta los siguientes WebViews por User-Agent:
- Instagram (todos los OS)
- Facebook (FBAN, FBAV, FB_IAB, FBIOS)
- Messenger, MessengerLite
- TikTok / BytedanceWebview
- Twitter, LinkedIn, WhatsApp, WeChat, Pinterest, Line, Snapchat

### En Android

El botón "Abrir" usa el `intent://` que fuerza la apertura en Chrome:
```
intent://18.218.192.129/Tienda#Intent;scheme=https;package=com.android.chrome;end
```

### En iOS

iOS no permite forzar salida del WebView. El banner muestra instrucciones para que el usuario lo haga manualmente desde el menú (•••).

### Cuándo NO aparece

- Si el visitante usa Safari/Chrome/Firefox normal (no es WebView)
- Si el visitante cerró el banner antes (sessionStorage)
- En las páginas de admin (`/Auth`, `/Dashboard`, `/Usuarios`, etc.) — el banner solo está en el template de Tienda

---

## F-34 — HTTPS (Pendiente)

**Acción tuya cuando registren el dominio:**

1. Comprar/asignar dominio (ej. `anamarcolmakeup.com`)
2. Apuntar DNS al servidor `18.218.192.129`
3. Instalar Let's Encrypt + certbot:
   ```bash
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d anamarcolmakeup.com -d www.anamarcolmakeup.com
   ```
4. En el `.htaccess` o config de Apache, forzar redirect HTTP → HTTPS:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```
5. Cuando HTTPS esté activo, el HSTS header (F-12) **se activa automáticamente** — no hay que tocar código

---

## Rollback si algo va mal

```bash
git revert HEAD     # revierte el commit de Sesión C
git push origin security-fixes
```

Después de eso, el código vuelve al estado previo. **No queda corrupción** porque:
- Csrf.php y WebViewDetector.php quedan en disco pero no se cargan si no se referencian
- Los headers HTTP se revierten al instante en la próxima request
- Los Controllers vuelven al `!==`

---

## Pendiente para sesiones siguientes

| Sesión | Cubre | Riesgo |
|--------|-------|--------|
| **C2** (opcional) | F-30 fix manual de Views — los ~30 textos de usuario | Bajo |
| **D** | F-05, F-06, F-15, F-16, F-17, F-18, F-21 — Auth crítico | Alto (toca login/sesiones activas) |
| **E** | F-08, F-23, F-32 — Rate limiter en BD | Alto (tabla nueva) |
