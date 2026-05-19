# Ana Marcol — Memoria de proyecto (handoff)

**Última actualización:** 2026-05-17
**Cliente:** Ana Marcol Makeup Studio
**Repo:** github.com/AngelPerez557/AnaMarcol (privado)
**Servidor:** http://18.218.192.129 (HTTP, sin dominio aún)

---

## Stack técnico (no negociable)

- PHP MVC puro (sin frameworks, sin Composer)
- XAMPP local + Ubuntu 22.04 en producción
- MySQL 8.0.45 / MariaDB local
- BD = `anamarcol` (173+ stored procedures, ~22 tablas)
- Todo SQL pasa por SPs, controllers NUNCA hacen queries directas
- Bootstrap 5.0.2 + FontAwesome 6.4 + SweetAlert2

## Convenciones del usuario

- **Modo grill-me** antes de codear módulos nuevos
- **Modo diagnose** estructurado para bugs
- **Modo caveman** = respuestas ultra-comprimidas
- Sin emojis en código
- Sin frases serviles
- MVC estricto, Clean Code, SOLID
- Paleta: rosa `#de777d` (light) + `#ffa1a8` (dark accent)

---

## Estado de la auditoría de seguridad

```
✅ Resueltos: 27 de 39 hallazgos (69%)

Sesión A: F-03/04/14/19/20/24/26/35  (Sin SQL injection, JRequest limpio, JRouter whitelist)
Sesión C: F-10/11/12/22/29/30/31/39  (CSP, hash_equals, Instagram WebView)
Sesión D: F-05/06/09/15/16/17/18/21  (Login CSRF, logout protegido, permisos TTL)
Sesión E: F-08/23/32                 (Rate limiter en BD)

⏳ Pendientes:
Sesión B: F-01/02/07/33 — `.env` (descartada, depende DevOps)
F-30: ~30 Views con XSS user content (bajo, CSP mitiga)
F-34: HTTPS (necesita dominio + Let's Encrypt)
F-37: cerrar MySQL :3306 a IPs externas (DevOps)
F-38: versionado de migraciones SQL (proceso)
```

## Otras mejoras aplicadas

- ✅ Tour rediseñado (sin emojis, multi-página, "Repetir tour" en menú)
- ✅ Scanner código de barras con cámara (Productos + Caja) — requiere HTTPS
- ✅ Optimización imágenes a WebP (calidad 92, max 1920px, sin pérdida visible)
- ✅ Dark mode rediseñado (grises cálidos, rosa suave, no negro stridente)
- ✅ Reporte de Inventario v2 (6 hojas Excel con catálogo completo + valor)

---

## Reportes — estado de cada uno

### Inventario v2 (HECHO)

| Componente | Estado |
|------------|--------|
| `BD/sql/reportes-inventario-v2.sql` | ✅ Listo (2 SPs nuevos) |
| `Models/ReporteModel.php` | ✅ +inventarioCompleto() +inventarioPorCategoria() |
| `Controllers/ReportesController.php::inventario()` | ✅ Carga nuevos datos + calcula valorTotalInventario |
| `Views/Reportes/Inventario.php` | ✅ Export Excel con 6 hojas |

**Deploy pendiente:**
1. Ejecutar `BD/sql/reportes-inventario-v2.sql` en producción
2. `git pull` del código

### Ventas v2 (PRÓXIMO CHAT)

**Falta agregar al export:**
- Detalle de cada venta (cliente, cajero, productos, totales)
- Items vendidos por venta
- Ventas anuladas con motivo y quién anuló
- Por cajero/usuario (quién vendió cuánto)
- Top clientes
- Filtro por rango de fechas en UI
- Comparativa mes actual vs anterior

**Implementar:**
- 5 SPs nuevos en `BD/sql/reportes-ventas-v2.sql`
- 5 métodos nuevos en `ReporteModel`
- Update Controller + View

### Pedidos v2 (DESPUÉS DE VENTAS)

**Falta:**
- Detalle de cada pedido (cliente, dirección, productos, estado, fechas)
- Pedidos por zona de envío
- Tiempo entre estados (analítica)
- Top productos pedidos online

---

## Archivos modificados en sesiones anteriores

```
Config/
├── Core/Auth.php            (rewrite — sesión única, refresh permisos)
├── Core/Csrf.php            (NUEVO — hash_equals)
├── Core/RateLimiter.php     (rewrite — BD)
├── Core/ImageOptimizer.php  (NUEVO — WebP)
├── Core/WebViewDetector.php (NUEVO — Instagram)
├── JRequest.php             (rewrite — sin saneo automático)
├── JRouter.php              (rewrite — whitelist)
├── AutoLoad.php             (regex)
└── Define.php               (mantenido — credenciales hardcoded por ahora)

Models/
├── ReporteModel.php         (NUEVO + v2)
├── RateLimitModel.php       (NUEVO)
└── UserModel.php            (+updateSessionToken)

Controllers/
├── AuthController.php       (CSRF login, sin SQL directo)
├── ReportesController.php   (rewrite)
├── 17 Controllers más       (Csrf::validate, ImageOptimizer)
└── TiendaController.php     (logout CSRF, fix favoritos)

Content/Dist/
├── css/am-tour.css          (NUEVO)
├── css/Custom/variables.css (paleta dark rediseñada)
├── css/login.css            (alineado a paleta)
├── js/am-tour.js            (NUEVO)
└── js/am-barcode-scanner.js (NUEVO)

Template/Default/
├── header.php  (CSS tour)
├── footer.php  (scripts tour + scanner)
└── menu.php    (logout con CSRF, repetir tour)

BD/sql/
├── sesion-A-2026-05-14.sql       (sp_users_updateSessionToken)
├── sesion-E-2026-05-14.sql       (tabla rate_limits + 3 SPs)
└── reportes-inventario-v2.sql    (2 SPs nuevos)

Index principal:
└── index.php                (CSP, HSTS condicional, Permissions-Policy)
```

---

## Pendientes priorizados para próximos chats

| Prioridad | Tarea | Tokens estimados |
|-----------|-------|------------------|
| 1 | **Reporte Ventas v2** (detalle, anuladas, por cajero, top clientes) | ~80K |
| 2 | **Reporte Pedidos v2** (detalle, por zona, tiempos entre estados) | ~70K |
| 3 | **Limpieza archivos huérfanos** (~30 bootstrap variants no usados) | ~15K |
| 4 | **Audit responsive** (necesita screenshots del usuario) | ~120K |
| 5 | **SEO tienda** (meta tags, sitemap, JSON-LD productos) | ~50K |
| 6 | **Cloudflare Tunnel para HTTPS** (instructivo para DevOps) | ~20K |
| 7 | **Audit upload security** (MIME, paths, ejecutables) | ~40K |
| 8 | **Modularización (post-Ana)** — extraer core + módulos opt-in | otro proyecto |

## Roadmap a futuro

1. Terminar reportes Ventas + Pedidos v2
2. Limpieza archivos huérfanos
3. HTTPS via Cloudflare Tunnel
4. Audit responsive con screenshots
5. SEO tienda antes de publicar en Instagram
6. Ana Marcol al 100%
7. **Modularización** → plantillas reusables para otros clientes (boilerplate DeskCod)

## Idea futura — Plantillización (después de Ana)

Convertir Ana en un **sistema modular** donde cada cliente activa solo lo que necesita:

```
Core obligatorio: Auth + RBAC + BD + helpers

Módulos opt-in:
- productos / caja-pos / facturacion / ventas / reportes
- pedidos-online / tienda-publica / citas / servicios / combos
- banners / galeria / zonas-envio / clientes-b2c / clientes-b2b
- favoritos / descuentos / inventario / wa-integration / soporte
```

Cada módulo en `modules/X/` con `module.json` declarando dependencias.
Wizard de instalación pregunta qué activar.

---

## Instrucciones para próximo chat

> "Leí AnaMarcol-handoff-memoria.md.
> Estado: 27/39 hallazgos seguridad cerrados, Inventario v2 hecho.
> Próxima tarea: Ventas v2.
> Continuar."

El próximo chat arranca con todo el contexto sin tener que re-explicar nada.
