# BRAND EXPERIENCE v.2 — Tesis del Camino B

**Estado:** Aprobado · Mockup `brand-dashboard-mockup-v2.html` validado visualmente
**Sesión origen:** Post-fix Driver.js (`351fbea`) + warm-up cosméticos (`c0abee2`)
**Audiencia del documento:** Founder team + Claude Code (executor) + sesiones futuras de supervisor
**Última actualización:** 2026-05-05 (rev 2 — incorpora decisiones sobre staff override, controller naming, y campaign_id hook)

---

## 1. Tesis en una frase

> El Brand de TrophyRoom no es un panel de admin con acceso a más cosas. Es **un producto distinto, dentro del mismo dominio**, con su propio mental model ("ads manager precario"), su propia navegación, y una superficie de analytics que existe sobre los datos reales de trofeos y badges — incluso cuando todavía no existe billing real, IA generativa, ni Campaigns como entidad de DB.

---

## 2. Por qué Camino B y no A

Se evaluaron dos caminos para la cuenta brand post-onboarding:

**Camino A — "Brand = Player con permisos extra"**
Un solo dashboard, mismo sidebar, los items de brand aparecen como secciones extra. Coste de implementación bajo. Costo conceptual alto: la cuenta brand se siente como una versión hinchada de la cuenta player, sin pricing power, sin language de marketing, sin sensación de "estoy administrando algo serio".

**Camino B — "Brand es un producto separado dentro del mismo login" (elegido)**
Sidebar diferenciado, dashboard rediseñado de cero con framing de ads manager, Studio rebrandeado como Campaign creator, métricas pricing-related visibles pero locked. Costo de implementación medio. Costo conceptual bajo: cuando el brand entra, *está en otra app*. Eso permite, más adelante, agregar billing/IA/targeting sin redefinir la experiencia — solo encendiendo features que ya tienen su lugar reservado en la UI.

**Razón de fondo:** TrophyRoom no compite con Steam o Discord en la cuenta player. En la cuenta brand compite (a futuro) con Meta Ads Manager, Google Ads, Twitch Sponsorships. Esa comparación define el lenguaje, no la implementación actual.

---

## 3. Principios de diseño

### 3.1. Functional analytics, locked pricing
Todo dato que se muestre tiene que ser real (trofeos forjados, badges otorgados, usuarios cross-hall). Lo que no es real se muestra **explícitamente como locked**: "CPT — coming soon", "Audience targeting — Pro", "AI builder — coming soon". Nunca un placeholder mentiroso, nunca un número inventado.

### 3.2. El framing es "ads manager precario"
No "panel de marca", no "admin de campañas", no "studio". Cuando un brand entra al dashboard, debe sentir que está mirando algo que se parece a Meta Ads Manager pero más temprano, más crudo. Esto baja la fricción de comprensión (el mental model ya existe en el usuario) y sube el techo (implica que vamos hacia ahí).

### 3.3. Studio = Campaign creator (rebranding mental, no técnico)
La página actual `Studio` (CRUD de trofeos) se mantiene funcional y técnicamente intacta. Solo cambia el lenguaje en sidebar y headers: "Studio" → posible rename futuro a "Campaigns" o "Builder". **No se toca código backend de trofeos en esta fase.**

### 3.4. Brand Hall público no se toca
`@hepamida` ya funciona. La vista pública del Brand Hall es independiente del dashboard interno y no entra en alcance de v.2.

### 3.5. Diferenciación por capability, no solo por account_type
El sidebar lee `account_type === 'brand'` para mostrar/ocultar secciones, **pero la authz real sigue siendo Spatie roles** (`brand_admin`, `tr_admin`, etc.). Esto importa porque:
- Un staff con rol `tr_admin` puede tener `account_type = 'player'` y aún así necesitar ver el dashboard brand para QA
- Un user con rol `brand_admin` siempre tiene `account_type = 'brand'` (lo asigna `POST /api/admin/brands/promote`)
- El sidebar usa account_type como heurística rápida; los endpoints del dashboard usan roles para autorización real

---

## 4. Arquitectura aprobada

### 4.1. Separación de superficies

| Superficie | Player ve | Brand ve | Notas |
|---|---|---|---|
| Sidebar items | Trophy Room, Forge, Achievements, Rewards, Feed, Dashboard | **Dashboard, Studio, Feed, Brand Hall** + (locked) Billing, AI Builder | Diferenciado por `account_type` |
| Dashboard | `DashboardOverview.vue` (player landing) | `BrandDashboard.vue` (analytics) | Componente raíz distinto, ruta `/dashboard` resuelve por account_type |
| Studio | No accesible | CRUD de trofeos (campaign creator) | Sin cambios funcionales en v.2 |
| Brand Hall público | Sí (lee `@hepamida`) | Sí (lee `@hepamida`) | Misma página, no cambia |
| Trophy Room / Forge / Achievements / Rewards | Sí | **No** | Brand no participa del economy player |

### 4.2. Layout del Brand Dashboard (mockup aprobado)

```
┌─────────────────────────────────────────────────────────────┐
│ HEADER · @hepamida brand session                            │
├─────────────────────────────────────────────────────────────┤
│ PERFORMANCE OVERVIEW                                        │
│ ┌─────────┬─────────┬─────────┬─────────┐                   │
│ │ Active  │Trophies │ Badges  │  CPT    │                   │
│ │pursuers │forged   │granted  │(locked) │                   │
│ │  847    │  +sparkline 30d  │ 2,143   │ — coming soon       │
│ └─────────┴─────────┴─────────┴─────────┘                   │
├─────────────────────────────────────────────────────────────┤
│ SECONDARY METRICS STRIP                                     │
│ Total badges · Cross-hall overlap · Multi-platform % ·      │
│ Achievement velocity                                        │
├─────────────────────────────────────────────────────────────┤
│ AUDIENCE INTELLIGENCE                                       │
│ ┌─────────┬─────────┬─────────┬─────────┐                   │
│ │Platforms│ Top     │Keywords │ Funnel  │                   │
│ │breakdown│achievs  │cross-   │compact  │                   │
│ │         │         │Discord  │         │                   │
│ └─────────┴─────────┴─────────┴─────────┘                   │
├─────────────────────────────────────────────────────────────┤
│ CAMPAIGNS + ACTIVITY (dual row)                             │
│ ┌─────────────────────────┬─────────────────────────┐       │
│ │ Campaigns table  (1.6fr)│ Activity feed   (1fr)   │       │
│ │ - Trophy name           │ - Forging events        │       │
│ │ - Status                │ - Badge grants          │       │
│ │ - Pursuers / Forges     │ - New pursuers          │       │
│ │ - Conversion %          │ - Cross-hall hits       │       │
│ └─────────────────────────┴─────────────────────────┘       │
├─────────────────────────────────────────────────────────────┤
│ PRO FEATURES LOCKED CTA                                     │
│ Real CPT · Audience targeting · AI builder · Benchmarking · │
│ Scheduled launches · Custom reports                         │
└─────────────────────────────────────────────────────────────┘
```

### 4.3. Backend: estado actual y deuda

**Lo que existe:**
- `BrandStatsController` → devuelve `linked_users`, `active_rules`, `synced_channels`, `badges_granted` (100% Discord-centric, hay que rehacerlo)
- Rutas `/api/brand/*` existen pero ninguna tiene analytics de pursuit/forging
- Spatie permissions instalado, roles `brand_admin`, `tr_admin`, `tr_moderator`, `tr_superadmin` definidos
- `account_type` enum en `users` table

**Lo que NO existe y hay que construir:**
- Endpoint de Performance Overview (4 hero cards + sparkline 30d)
- Endpoint de Secondary Metrics Strip (4 datos)
- Endpoint de Audience Intelligence (4 cards)
- Endpoint de Campaigns table (lista de trofeos del brand con stats)
- Endpoint de Activity Feed (eventos cronológicos)

**Lo que SÍ se construye en v.2 a nivel DB (mínimo, hook-only):**
- Migration: agregar `campaign_id` nullable + indexed en `trophies`. No se llena, no se expone en UI, no se usa en queries de v.2. Existe para que la futura agrupación funcional no requiera backfill de datos en producción.

**Lo que NO se construye en v.2:**
- Billing real / Stripe
- IA generativa
- Tabla `campaigns` propiamente dicha + CRUD + agrupación funcional (las "campaigns" en la UI son trofeos del brand, agrupados por la query, 1:1 trofeo-campaign por ahora)
- `achievements_posts` separado del feed (el feed actual los resuelve)

### 4.4. Frontend: estado actual y deuda

**Lo que existe:**
- `BrandDashboard.vue` (5KB) — vacío conceptualmente, se reescribe
- `DashboardOverview.vue` (15KB) — landing del player, no se toca pero el router debe diferenciar
- Sidebar tiene `isBrand` e `isStaff` getters; **falta guard por account_type para items player**

**Lo que hay que construir:**
- Reescritura de `BrandDashboard.vue` siguiendo el layout del mockup
- Componentes hijos: `PerformanceOverview.vue`, `SecondaryMetrics.vue`, `AudienceIntelligence.vue`, `CampaignsTable.vue`, `ActivityFeed.vue`, `LockedProFeatures.vue`
- Refactor de sidebar con guards por `account_type`
- Mocks de data realista para llenar los gráficos durante development (necesario porque los endpoints se construyen en paralelo)

---

## 5. Lo que NO está en alcance de v.2 (explícito para evitar scope creep)

| Item | Razón | Cuándo |
|---|---|---|
| Billing real / Stripe | El framing locked permite sostener la narrativa sin construirlo | v.3 o cuando haya primer brand pago |
| IA generativa (campaign builder) | Tiene su lugar en sidebar como "coming soon", no se construye | Post-validación de pricing |
| Campaign como entidad de DB completa (tabla `campaigns`, CRUD, agrupación funcional) | UX framing en v.2 muestra trofeos como filas de campaigns; **se agrega `campaign_id` nullable en `trophies` ahora** como hook para agrupación futura sin migración de datos | Tabla `campaigns` y agrupación funcional cuando un brand pida ≥2 trofeos bajo una misma campaña |
| Achievement posts separados | El feed actual ya los resuelve | Si la densidad del feed se vuelve un problema |
| Audience targeting funcional | Locked en UI | Cuando exista billing |
| Scheduled launches | Locked en UI | Cuando exista billing |
| Sector benchmarking | Necesita >5 brands activas para tener sentido estadístico | Post-tracción |

---

## 6. Criterios de éxito de la v.2

1. **Un brand entra al dashboard y entiende qué está mirando en <30s.** El framing de ads manager debe ser legible sin tutorial.
2. **Todos los números mostrados son reales.** Ningún placeholder mentiroso, ningún hardcode disfrazado.
3. **Los features locked se ven aspiracionales, no rotos.** El usuario debe pensar "esto va a estar pronto" no "esto está roto".
4. **Cero regresión en el player experience.** El refactor de sidebar no rompe ningún item del player.
5. **El brand puede crear/editar trofeos desde Studio sin cambios respecto a hoy.** Studio sigue funcional 1:1.
6. **Sidebar respeta `account_type` y roles.** Un staff puede impersonar brand para QA sin perder sus permisos de admin.

---

## 7. Riesgos identificados

| Riesgo | Mitigación |
|---|---|
| Endpoints nuevos lentos por queries pesadas sobre `badge_user` y `trophy_user` | Definir índices en el brief de Claude Code, considerar cache de 5min en analytics |
| Mocks de development se quedan en producción | Convención clara: `frontend/src/mocks/brand-dashboard.ts` que se descarta por feature flag, y se borra antes del primer deploy a prod |
| Sidebar con guard por `account_type` rompe a un staff que necesita ver ambos | Override por rol: si el user tiene rol `tr_admin` ve todo el sidebar (player + brand), no se aplica guard |
| Mockup HTML diverge del Vue por modificaciones no propagadas | El mockup es source of truth; cualquier cambio visual debe primero modificar `brand-dashboard-mockup-v2.html`, después llegar al Vue |
| BrandStatsController existente queda como código zombie | **Decisión:** crear `BrandAnalyticsController` nuevo con namespace `/api/brand/analytics/*`; el viejo se borra cuando el dashboard nuevo esté verde en producción. Razón: el controller viejo es 100% Discord-centric y reescribirlo en el mismo namespace deja contaminación semántica para futuras sesiones. |

---

## 8. Próximos artefactos (entregables de esta sesión)

1. **Este documento** (`BRAND_EXPERIENCE_V2_THESIS.md`) → commit al repo en `docs/` o raíz
2. **Brief operativo para Claude Code** (`CLAUDE_BRAND_DASHBOARD_OPS.md`) — pendiente, paso 2 del plan de hoy. Incluirá:
   - Lista de endpoints backend nuevos con shape exacto de response
   - Refactor sidebar.vue con guards por account_type + override por rol
   - Reescritura BrandDashboard.vue + componentes hijos (orden de implementación)
   - Mocks de data realista (con marca clara para borrar pre-deploy)

---

**Stable commit anchor antes de empezar v.2:** `c0abee2`
**Branch sugerida:** `feature/brand-dashboard-v2`
**Mockup aprobado:** `brand-dashboard-mockup-v2.html`
