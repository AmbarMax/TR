# CLAUDE_PHASE_9B_OPS.md — Phase 9B: Sidebar redesign with hybrid SVG/pixel-art icons

> **Audience:** Claude Code (CLI). Run from `~/Documents/trophyroom` after `git checkout main && git pull`.
> **Read first:** `CLAUDE.md` + this brief.
> **Scope:** Rediseño completo del sidebar (`.front-sidebar`) con sistema híbrido de íconos: SVG custom como estado default, pixel-art raptor en hover/active. Incluye logout al pie del sidebar (resuelve regresión de 9A).

---

## Goal

Reemplazar el sidebar actual con una versión rediseñada que:

1. Usa SVG custom (con hints de T-Rex como cresta chartreuse y fill naranja tenue) como estado default de íconos.
2. En hover de un ítem **inactivo**: el SVG se encoge con snap (pop-out) y un sprite pixel-art raptor entra rebotando con easing elástico (pop arcade).
3. En estado active: el pixel-art queda permanente con idle bob sutil (2px vertical cada 1.6s).
4. Agrega un botón **Logout** al pie del sidebar (resuelve regresión de 9A donde se perdió del UI).
5. Soporta 6 ítems: Dashboard, Trophy Room, Forge, Rewards, Achievements, Admin Panel.
6. Preserva toda la funcionalidad: mobile toggle (`sideBarStatus`), router-link active binding, social icons (Twitter/Discord/Discord-alt), v-if mobile.

---

## Non-goals

- NO tocar `main-header.vue` (ya rediseñado en 9A).
- NO tocar páginas de contenido (Dashboard, Trophy Room, etc. — son 9D+).
- NO tocar `App.vue` ni el sistema de atmósfera global (completado en 9A).
- NO modificar legacy components (`achievement-card.vue`, `forge-card.vue`, `validate-card.vue`).
- NO tocar backend, rutas, Vuex actions.
- NO modificar la lógica de auth ni el router.
- NO remover Bootstrap de otras páginas (solo migrar donde toquemos — en este brief, solo el sidebar).

---

## Assets del brief

Este brief viene con **6 sprites PNG** que hay que colocar en el repo (uno por cada ítem del sidebar):

```
resources/web/images/web/img/mascot/
├── raptor-run.png          (Dashboard)
├── raptor-trophy.png       (Trophy Room)
├── raptor-forge.png        (Forge)
├── raptor-rewards.png      (Rewards)
├── raptor-podium.png       (Achievements)
└── raptor-admin.png        (Admin Panel)
```

Los archivos son 128×128 PNG transparentes con arte pixel-art del raptor. Max los entrega junto a este brief.

**Nota de coherencia visual:** Los 4 primeros (run/trophy/forge/podium) tienen un estilo NES/16-bit clean más consistente. Los 2 últimos (rewards/admin) son variantes con un poco más de detalle/shading. Se usan tal cual por ahora; pueden regenerarse más adelante si Max decide afinar consistencia — no es bloqueante.

---

## Steps

### Step 1 — Colocar los sprites PNG en el repo

**Acción:** Crear el directorio si no existe y confirmar que Max colocó los 6 PNG.

```bash
mkdir -p resources/web/images/web/img/mascot
```

**Verificar:**
```bash
ls -la resources/web/images/web/img/mascot/
```

Debe listar **los 6 archivos**: `raptor-run.png`, `raptor-trophy.png`, `raptor-forge.png`, `raptor-rewards.png`, `raptor-podium.png`, `raptor-admin.png`.

Si algún archivo falta, **NO avanzar** — pedir los sprites faltantes antes de continuar.

**Commit:** `feat: add raptor pixel-art sprites for sidebar mascot system`

---

### Step 2 — Reemplazar sidebar.vue

**Archivo:** `resources/web/js/components/sidebar.vue`

**Acción:** Reemplazar el `<template>` completo y el bloque `<style>` completo. El `<script>` se conserva (tiene la lógica de `sideBarStatus`, `isMobile`, `closeSideBar` que sigue siendo válida) — **leer el script actual primero** para preservarlo exactamente como está.

#### Template nuevo (reemplaza completo)

```vue
<template>
  <aside class="front-sidebar" v-if="sideBarStatus || !isMobile">
    <!-- Logo -->
    <div class="front-sidebar_logo">
      <router-link to="/trophy-room">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom">
      </router-link>
      <button v-if="isMobile" class="sidebar-close" @click="closeSideBar" aria-label="Close menu">
        <img src="../../../web/images/web/img/icons/close.svg" alt="Close">
      </button>
    </div>

    <!-- Nav -->
    <nav class="sidebar_menu">
      <!-- Dashboard -->
      <router-link to="/dashboard" class="nav-item" :class="{ active_item: $route.path === '/dashboard' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M3 11 L12 4 L21 11 L21 20 L15 20 L15 14 L9 14 L9 20 L3 20 Z" fill="rgba(255,97,0,0.2)"/>
              <path d="M10 7 L14 7" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-run')"></span>
        </span>
        <span class="nav-label">Dashboard</span>
      </router-link>

      <!-- Trophy Room -->
      <router-link to="/trophy-room" class="nav-item" :class="{ active_item: $route.path === '/trophy-room' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M7 4 L17 4 L17 10 C17 13 15 15 12 15 C9 15 7 13 7 10 Z"/>
              <path d="M7 6 C4 6 3 7 3 9 C3 11 5 12 7 12"/>
              <path d="M17 6 C20 6 21 7 21 9 C21 11 19 12 17 12"/>
              <path d="M9 15 L9 18 L15 18 L15 15"/>
              <path d="M8 20 L16 20"/>
              <path d="M10 7 L14 7" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-trophy')"></span>
        </span>
        <span class="nav-label">Trophy Room</span>
      </router-link>

      <!-- Forge -->
      <router-link to="/forge" class="nav-item" :class="{ active_item: $route.path === '/forge' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M15 4 L22 7 L18 10 L15 7 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M4 14 L18 14 L20 16 L16 18 L4 18 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M8 18 L8 21 M14 18 L14 21"/>
              <circle cx="17" cy="8" r="1.5" fill="#c1f527" stroke="none"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-forge')"></span>
        </span>
        <span class="nav-label">Forge</span>
      </router-link>

      <!-- Rewards -->
      <router-link to="/rewards" class="nav-item" :class="{ active_item: $route.path === '/rewards' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M3 10 L21 10 L21 20 L3 20 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M3 10 L3 7 L21 7 L21 10"/>
              <path d="M11 7 L11 20 M13 7 L13 20"/>
              <circle cx="12" cy="13" r="1.3" fill="#c1f527" stroke="none"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-rewards')"></span>
        </span>
        <span class="nav-label">Rewards</span>
      </router-link>

      <!-- Achievements -->
      <router-link to="/feed" class="nav-item" :class="{ active_item: $route.path === '/feed' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M4 5 L20 5 L20 9 L16 12 L12 11 L8 12 L4 9 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M12 11 L12 16"/>
              <path d="M8 19 L16 19 L15 16 L9 16 Z"/>
              <path d="M7 6 L10 6" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-podium')"></span>
        </span>
        <span class="nav-label">Achievements</span>
      </router-link>

      <!-- Admin Panel -->
      <router-link to="/brand-dashboard" class="nav-item" :class="{ active_item: $route.path === '/brand-dashboard' }">
        <span class="nav-icon">
          <span class="nav-icon-svg">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round">
              <path d="M12 3 L19 6 L19 12 C19 16 16 19 12 20 C8 19 5 16 5 12 L5 6 Z" fill="rgba(255,97,0,0.15)"/>
              <path d="M9 12 L11 14 L15 10" stroke="#c1f527" stroke-width="2.5"/>
            </svg>
          </span>
          <span class="nav-icon-pixel" :style="pixelStyle('raptor-admin')"></span>
        </span>
        <span class="nav-label">Admin Panel</span>
      </router-link>
    </nav>

    <!-- Logout (restored in 9B, was lost in 9A header refactor) -->
    <button class="sidebar-logout" @click="handleLogout" aria-label="Logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
        <path d="M16 17l5-5-5-5M21 12H9M13 21H5V3h8"/>
      </svg>
      <span>Logout</span>
    </button>

    <!-- Social footer (preserved from legacy) -->
    <div class="sidebar-foot">
      <a href="https://twitter.com/trophyroom" target="_blank" rel="noopener" class="social" aria-label="Twitter">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
      <a href="https://discord.gg/trophyroom" target="_blank" rel="noopener" class="social" aria-label="Discord">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4C2.9 4 2 4.9 2 6v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM9.5 16c-1.4 0-2.5-1.1-2.5-2.5S8.1 11 9.5 11s2.5 1.1 2.5 2.5S10.9 16 9.5 16zm5 0c-1.4 0-2.5-1.1-2.5-2.5S13.1 11 14.5 11s2.5 1.1 2.5 2.5S15.9 16 14.5 16z"/></svg>
      </a>
    </div>
  </aside>
</template>
```

**Notas importantes del template:**

- Se conservan los `router-link` originales con el mismo `$route.path ===` check que ya existía. NO cambiar a `router-link-active` nativo — el proyecto ya usa este patrón.
- El path de achievements es `/feed` (no `/achievements`), según el router actual.
- El path de admin es `/brand-dashboard`, según el router actual.
- Si algún path no existe en el router, Claude Code debe **verificar con `grep -n "path:" resources/web/js/router/routes.js`** antes de dejarlo, y ajustar si corresponde.
- **NO remover** el `v-if="sideBarStatus || !isMobile"` — es la lógica de toggle mobile.
- **NO remover** el botón close mobile `@click="closeSideBar"` — sigue siendo necesario.

#### Script — modificaciones mínimas

Leer el `<script>` actual. Conservar **todo** el código existente (imports, computed, methods, data). Agregar **únicamente**:

1. Al objeto `methods`, agregar:

```js
pixelStyle(spriteName) {
  return {
    backgroundImage: `url(${this.spritePath(spriteName)})`
  };
},
spritePath(spriteName) {
  // Vite resolves this at build time via asset import
  return new URL(`../../../web/images/web/img/mascot/${spriteName}.png`, import.meta.url).href;
},
async handleLogout() {
  // Preserve whatever logout flow the project uses.
  // If the store has a logout action, call it:
  try {
    if (this.$store && typeof this.$store.dispatch === 'function') {
      await this.$store.dispatch('logout');
    }
  } catch (e) {
    // ignore
  }
  // Clear local auth tokens (fallback)
  try {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  } catch (e) {
    // ignore
  }
  // Redirect to login
  this.$router.push({ path: '/login' });
}
```

**IMPORTANTE sobre `handleLogout`:** Antes de pegar esto, Claude Code debe buscar si ya existe una acción Vuex de logout en el store con:

```bash
grep -rn "logout" resources/web/js/store/
```

Si existe una acción con otro nombre (ej. `signOut`, `userLogout`, `clearSession`), ajustar el `dispatch` a ese nombre. Si no existe ninguna, el fallback de localStorage + redirect funciona.

También buscar cómo se maneja el token actual:

```bash
grep -rn "localStorage.*token\|localStorage.*user" resources/web/js/
```

Ajustar las claves de `localStorage.removeItem` a las reales (pueden ser `auth_token`, `jwt`, `access_token`, etc.).

#### Style (reemplaza completo el `<style>` actual)

```vue
<style lang="scss" scoped>
/* Sidebar container */
.front-sidebar {
  width: 270px;
  min-width: 270px;
  height: 100vh;
  min-height: 660px;
  position: sticky;
  top: 0;
  left: 0;
  z-index: 30;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  padding: 26px 0 18px;
  background: rgba(5, 5, 8, 0.75);
  backdrop-filter: blur(14px) saturate(1.2);
  -webkit-backdrop-filter: blur(14px) saturate(1.2);
  border-right: 1px solid rgba(255, 97, 0, 0.12);
  box-shadow: inset -1px 0 0 rgba(255, 97, 0, 0.05);
}

/* Logo area */
.front-sidebar_logo {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 20px;
  margin-bottom: 28px;
  position: relative;
}
.front-sidebar_logo a {
  display: flex;
  align-items: center;
  justify-content: center;
}
.front-sidebar_logo img {
  width: 58px;
  height: 58px;
  filter: drop-shadow(0 0 14px rgba(255, 97, 0, 0.4));
}
.sidebar-close {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 6px;
  cursor: pointer;
}
.sidebar-close img {
  width: 18px;
  height: 18px;
}

/* Nav menu */
.sidebar_menu {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 0 10px;
  flex: 1;
  list-style: none;
  margin: 0;
}

/* Nav item (router-link) */
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  font-size: 11px;
  color: var(--text-muted);
  border-left: 2px solid transparent;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
  text-decoration: none;
}
.nav-item:hover {
  color: var(--text);
  background: rgba(255, 97, 0, 0.04);
}
.nav-item.active_item {
  color: var(--primary);
  background: linear-gradient(90deg, rgba(255, 97, 0, 0.14), rgba(255, 97, 0, 0.02));
  border-left-color: var(--primary);
  text-shadow: 0 0 10px rgba(255, 97, 0, 0.4);
}
.nav-item.active_item::before {
  content: '';
  position: absolute;
  left: -2px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--primary);
  box-shadow: 0 0 14px var(--primary);
}

.nav-label {
  flex: 1;
  min-width: 0;
}

/* ========== ICON HYBRID SYSTEM (SVG default + pixel-art hover/active) ========== */
.nav-icon {
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.nav-icon-svg {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.1s cubic-bezier(0.6, 0, 0.4, 1), opacity 0.1s;
  color: inherit;
}
.nav-icon-svg svg {
  width: 22px;
  height: 22px;
  color: inherit;
}
.nav-icon-pixel {
  position: absolute;
  inset: 0;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: center;
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
  image-rendering: crisp-edges;
  transform: scale(0);
  opacity: 0;
  transition:
    transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s,
    opacity 0.08s 0.1s;
}

/* Hover on non-active: SVG out, pixel pops in */
.nav-item:hover:not(.active_item) .nav-icon-svg {
  transform: scale(0);
  opacity: 0;
  transition-delay: 0s;
}
.nav-item:hover:not(.active_item) .nav-icon-pixel {
  transform: scale(1);
  opacity: 1;
  transition:
    transform 0.18s cubic-bezier(0.34, 1.8, 0.64, 1) 0.08s,
    opacity 0.08s 0.08s;
}

/* Active state: pixel permanent + idle bob */
.nav-item.active_item .nav-icon-svg {
  transform: scale(0);
  opacity: 0;
}
.nav-item.active_item .nav-icon-pixel {
  transform: scale(1);
  opacity: 1;
  animation: sidebar-idle-bob 1.6s ease-in-out infinite;
  filter: drop-shadow(0 0 6px rgba(255, 97, 0, 0.35));
}

@keyframes sidebar-idle-bob {
  0%, 100% {
    transform: scale(1) translateY(0);
  }
  50% {
    transform: scale(1) translateY(-2px);
  }
}

/* Motion reduction */
@media (prefers-reduced-motion: reduce) {
  .nav-icon-pixel,
  .nav-icon-svg {
    transition: none;
  }
  .nav-item.active_item .nav-icon-pixel {
    animation: none;
  }
}

/* ========== LOGOUT ========== */
.sidebar-logout {
  margin: auto 10px 4px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.12em;
  text-transform: uppercase;
  border-top: 1px dashed rgba(255, 97, 0, 0.1);
  padding-top: 14px;
  cursor: pointer;
  background: none;
  border-left: none;
  border-right: none;
  border-bottom: none;
  font-family: inherit;
  transition: color 0.15s;
  text-align: left;
  width: calc(100% - 20px);
}
.sidebar-logout:hover {
  color: var(--primary);
}
.sidebar-logout svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

/* ========== SOCIAL FOOTER ========== */
.sidebar-foot {
  padding: 14px 18px 0;
  display: flex;
  gap: 16px;
  align-items: center;
  justify-content: center;
  border-top: 1px solid rgba(255, 97, 0, 0.08);
}
.social {
  color: var(--text-dim);
  transition: all 0.15s;
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.social:hover {
  color: var(--primary);
  filter: drop-shadow(0 0 4px var(--primary));
}

/* ========== MOBILE ========== */
@media (max-width: 768px) {
  .front-sidebar {
    width: 100%;
    min-width: 0;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
  }
}
</style>
```

**Verificar después de este step:**

```bash
npm run dev
```

Abrir `/dashboard` en desktop:
- [ ] Sidebar aparece con 6 items + logo arriba + logout + social abajo.
- [ ] Dashboard tiene el border-left naranja glow, fondo degradado tenue, y **el raptor pixel-art visible** con idle bob sutil (2px vertical cada 1.6s).
- [ ] Hovering sobre Trophy Room / Forge / Rewards / Achievements / Admin: el SVG hace pop-out rápido y el raptor entra con rebote elástico.
- [ ] El active item NO hace la transición (ya está en estado pixel).
- [ ] Los 6 sprites son distintos: raptor corriendo (Dashboard), con trofeo (Trophy Room), con martillo (Forge), con cofre (Rewards), en podio (Achievements), con corona (Admin).
- [ ] Click en un nav-item cambia de ruta Y el active state se mueve al nuevo.
- [ ] Click en Logout: limpia sesión y redirige a `/login`.
- [ ] Console sin errores nuevos (ignorar warnings pre-existentes de Bootstrap/Tailwind nesting).

Mobile (DevTools 375px):
- [ ] Sidebar se oculta por default (`v-if`).
- [ ] Si se agrega un botón para abrir sidebar, funciona. **NO implementar ese botón acá** — existe en el header legacy o en otro sitio; solo verificar que `sideBarStatus` se respeta.
- [ ] Al abrirse, el botón close (X) funciona.

**Commit:** `feat: redesign sidebar with hybrid SVG + pixel-art icons, restore logout`

---

### Step 3 — Limpiar overlap con legacy styles del sidebar

**Archivo:** `resources/web/css/style.scss`

**Acción:** Las reglas del sidebar legacy en `style.scss` (líneas ~130-230 aprox.) van a entrar en conflicto con el `<style scoped>` del componente. Los estilos scoped ganan en especificidad, pero hay reglas globales que pueden bleedear.

**Buscar** las siguientes clases en `style.scss` y **comentar** (no eliminar todavía — por si hay que rollback):

```bash
grep -n "^\.front-sidebar\|^\.front-sidebar_logo\|^\.sidebar_menu\|^\.nav-item\|^\.active_item" resources/web/css/style.scss
```

Por cada regla encontrada que aplique al sidebar (no al `.nav-item` de header-nav u otros lados), **prefijar la regla con un comentario SCSS**:

```scss
// DEPRECATED 9B — replaced by scoped styles in sidebar.vue
// .front-sidebar {
//   ...
// }
```

**IMPORTANTE:** No comentar reglas que no sean del sidebar. El archivo tiene muchas reglas. Si hay dudas sobre una regla específica, preservarla y comentarla con `// REVIEW 9B` para que Max decida después.

**Verificar:** `npm run dev` sin errores de SASS. El sidebar se sigue viendo igual que al final del Step 2 (los estilos scoped están intactos).

**Commit:** `refactor: deprecate legacy sidebar styles in style.scss`

---

### Step 4 — Verificar que no hay assets rotos

**Acción:** Abrir DevTools → Network, filtrar por "img". Los 4 PNG del mascot deben aparecer con status 200.

Si alguno da 404, verificar path. Los sprites deben estar en `resources/web/images/web/img/mascot/` y Vite debería resolverlos a `/build/assets/raptor-*.png` después del build.

Si el `new URL(..., import.meta.url)` no funciona en el contexto del proyecto (algunos setups de Vite viejos no lo soportan), alternativa: importar los sprites al tope del `<script>` de sidebar.vue:

```js
import raptorRun from '../../../web/images/web/img/mascot/raptor-run.png';
import raptorTrophy from '../../../web/images/web/img/mascot/raptor-trophy.png';
import raptorForge from '../../../web/images/web/img/mascot/raptor-forge.png';
import raptorRewards from '../../../web/images/web/img/mascot/raptor-rewards.png';
import raptorPodium from '../../../web/images/web/img/mascot/raptor-podium.png';
import raptorAdmin from '../../../web/images/web/img/mascot/raptor-admin.png';

export default {
  // ...
  data() {
    return {
      sprites: {
        'raptor-run': raptorRun,
        'raptor-trophy': raptorTrophy,
        'raptor-forge': raptorForge,
        'raptor-rewards': raptorRewards,
        'raptor-podium': raptorPodium,
        'raptor-admin': raptorAdmin
      }
    };
  },
  methods: {
    pixelStyle(name) {
      return { backgroundImage: `url(${this.sprites[name]})` };
    }
  }
};
```

Usar esta versión solo si el `new URL` falla (probar primero). Ambas funcionan en Vite 4.

**Commit (si se hizo el fallback):** `fix: import sprites as modules for Vite compatibility`

---

### Step 5 — Build y deploy

```bash
npm run build
```

Verificar sin errores. Si el build crashea por los PNG (ej. "cannot find module"), volver al Step 4 y usar el fallback.

Deploy:

```bash
cd ~/Documents/trophyroom && git add -A && git commit -m "feat: Phase 9B — sidebar redesign complete" && git push origin main && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build && chown -R www-data:www-data storage bootstrap/cache public/build && chmod -R 775 storage bootstrap/cache && rm -rf storage/framework/views/*"
```

Verificar hashes:

```bash
cd ~/Documents/trophyroom && git log --oneline -1 && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git log --oneline -1"
```

Ambos deben coincidir.

---

## Verification checklist (lo que Max chequea post-deploy)

Abrir `https://app.ambar.gg/dashboard` con hard refresh (Cmd+Shift+R) o ventana de incógnito.

### Visual
- [ ] Sidebar de 270px a la izquierda con logo TR arriba y 6 ítems del menú.
- [ ] Dashboard está activo con el raptor pixel-art corriendo, con idle bob (2px cada 1.6s). Border-left naranja glow.
- [ ] Los otros 5 ítems muestran el SVG custom (cresta chartreuse en Dashboard/Trophy/Achievements, punto chartreuse en Forge/Rewards/Admin).
- [ ] Hover sobre cualquier ítem inactivo: SVG se encoge y el raptor pixel-art entra rebotando con elastic easing.
- [ ] Al mover el mouse fuera: el raptor se encoge y el SVG vuelve con snap.
- [ ] Click en Trophy Room: raptor volando con trofeo.
- [ ] Click en Forge: raptor con martillo y yunque.
- [ ] Click en Rewards: raptor con cofre abierto.
- [ ] Click en Achievements (ruta `/feed`): raptor en podio.
- [ ] Click en Admin Panel: raptor con corona.

### Funcional
- [ ] Click en cualquier nav-item cambia la ruta y el active state correctamente.
- [ ] Click en Logout: redirige a `/login` y al volver a `/dashboard` manualmente pide auth.
- [ ] Twitter y Discord abren en tab nueva (target="_blank").

### Mobile (DevTools 375px)
- [ ] Sidebar oculto por default.
- [ ] Al abrirlo (con el mecanismo existente), ocupa toda la pantalla y tiene el close button.
- [ ] La interacción hover → pixel-art funciona también con tap (o se omite — en mobile el usuario ve siempre el SVG hasta tocar).

### Performance
- [ ] Navegar entre 5-6 páginas: sidebar no lagguea, transiciones son smooth.
- [ ] DevTools Performance: animation del idle bob no causa repaint excesivo (debería ser GPU-composited por el `transform`).

### Console
- [ ] Sin errores nuevos en consola.
- [ ] Network: los 6 PNG del mascot cargan con 200.

---

## Qué reportar a claude.ai

1. Los commits creados (hashes + mensajes).
2. Confirmación de hashes local/server coincidentes.
3. Capturas:
   - Desktop `/dashboard` con sidebar completo visible
   - Hover sobre un ítem inactivo (Trophy Room) capturando el estado pixel-art
   - Click en otro ítem (ej. Forge) con estado activo
   - Mobile 375px con sidebar abierto
4. Cualquier ajuste de paths que hayas hecho respecto al brief (ej. si `routes.js` tenía `/achievements` en vez de `/feed`).
5. Si el `new URL` falló y usaste el fallback de import.
6. Si algún sprite no cargó y mostró 404.

---

## Rollback plan

Si algo rompe:

```bash
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git reset --hard HEAD~N && npm run build"
```

Donde N es el número de commits creados en 9B. Típicamente 2-4.

---

## Backlog después de 9B

Items que quedan pendientes para brief posterior:

1. **Coherencia visual de sprites:** Los 6 sprites se usan tal cual, pero `raptor-rewards.png` y `raptor-admin.png` tienen un estilo ligeramente más detallado/shaded que los otros 4 (NES clean). Opcional regenerarlos en un brief futuro para matchear estilo.
2. **Mobile toggle button:** El brief asume que ya hay un botón en el main-header o en otro lado que abre el sidebar en mobile (probablemente vía `sideBarStatus`). Si no existe, agregarlo en brief de UI polish.
3. **Animación hover en mobile:** En touch devices el hover no existe — considerar usar `:active` o un tap-hold, o desactivar la animación en mobile. Decisión para 9M.
