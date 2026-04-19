# CLAUDE_PHASE_9A_OPS.md — Phase 9A: Global Tokens, Atmosphere & Main Header

> **Audience:** Claude Code (CLI). Run from `~/Documents/trophyroom` after `git checkout main && git pull`.
> **Read first:** `TROPHYROOM_WORKING_GUIDE.md` (rules + deploy flow).
> **Scope:** Base del rediseño 2.0 — design tokens, fuentes, capas atmosféricas globales (hex + grid + radial + scanlines + CRT vignette + flicker), header sticky con currencies. NO toca sidebar (9B), NO toca páginas (9D+), NO toca componentes legacy.

---

## Goal

Dejar la app lista para que todas las phases siguientes (9B–9M) puedan construir encima de una base visual unificada. Al terminar 9A, una ruta autenticada cualquiera debe mostrar:

1. Fondo atmosférico completo (hex pattern + grid mask + radials + scanlines + flicker + vignette) — visible globalmente, incluida la página de login.
2. Fuente `Share Tech Mono` para body/UI + `VT323` disponible como `var(--display)` para displays grandes (aún no usada salvo en header).
3. Header sticky `main-header.vue` con breadcrumb + wallet-rail (Ambar/Uru en VT323) + bell + avatar-pill.
4. Sidebar y páginas existentes siguen funcionando exactamente igual que antes (ningún componente roto).

El resto del rediseño se construye encima de esto.

---

## Non-goals (NO hacer en esta phase)

- NO rediseñar el sidebar (es 9B).
- NO tocar el T-Rex / mascota (es 9B).
- NO rediseñar Dashboard, Trophy Room, Forge, Rewards, Achievements ni ninguna página (son 9D+).
- NO modificar los legacy cards (`achievement-card.vue`, `forge-card.vue`, `validate-card.vue`).
- NO tocar backend, routes, Vuex ni API calls.
- NO remover clases Bootstrap existentes de otras páginas (solo se migra donde se toca).
- NO tocar `SinglePage.vue` layout (el virtual hall público — 9F).

---

## Context visual (referencia)

Los HTMLs standalone aprobados definen la atmósfera. En ellos las capas están dentro de cada page, pero acá las centralizamos **una sola vez en `App.vue`** para que toda la app (Main, Auth, SinglePage) las herede sin duplicar.

Tokens de referencia — deben quedar exactamente estos valores:

```
--bg:#000003
--surface:#0e0f11
--surface-2:#1a1c1f
--surface-3:#252729
--primary:#ff6100
--primary-glow:rgba(255,97,0,0.45)
--accent:#c1f527
--accent-glow:rgba(193,245,39,0.35)
--text:#feeddf
--text-muted:#9a9590
--text-dim:#5a5550
--border:#2a2c2e
--mono:'Share Tech Mono',monospace
--display:'VT323',monospace
```

---

## Steps

### Step 1 — Instalar VT323 en index.html (entry point HTML)

**Archivo:** `resources/web/index.html` (o el HTML que Vite usa como entry; si está en `public/index.html` o en `resources/views/web.blade.php`, aplicar ahí).

**Acción:** Localizar el `<head>` y buscar la línea existente que carga Share Tech Mono desde Google Fonts. Reemplazar el `<link>` de fonts por el siguiente bloque:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323&display=swap" rel="stylesheet">
```

Si Share Tech Mono se cargaba desde otro origen (ej. `@import` en SCSS), **eliminar ese import** y dejar solo el `<link>` del HTML — fuentes siempre desde `<head>` para evitar FOUT.

**Verificar:** `npm run dev`, abrir DevTools → Network → filtrar por `font`. Deben aparecer los dos archivos de font (`sharetechmono-*.woff2` y `vt323-*.woff2`) con status 200.

**Commit:** `feat: add VT323 display font to head`

---

### Step 2 — Escribir design tokens + atmosphere + base typography en SCSS global

**Archivo:** `resources/web/css/style.scss`

**Acción:** Al inicio del archivo (antes de cualquier import o regla existente), agregar el bloque completo de abajo. Si ya existen tokens `:root` definidos, **reemplazarlos** por este bloque (no duplicar `:root`). Mantener el resto del archivo intacto.

```scss
/* ========== DESIGN TOKENS (TrophyRoom 2.0) ========== */
:root {
  --bg: #000003;
  --surface: #0e0f11;
  --surface-2: #1a1c1f;
  --surface-3: #252729;
  --primary: #ff6100;
  --primary-glow: rgba(255, 97, 0, 0.45);
  --accent: #c1f527;
  --accent-glow: rgba(193, 245, 39, 0.35);
  --text: #feeddf;
  --text-muted: #9a9590;
  --text-dim: #5a5550;
  --border: #2a2c2e;
  --mono: 'Share Tech Mono', monospace;
  --display: 'VT323', monospace;
}

/* ========== BASE RESET & TYPOGRAPHY ========== */
*, *::before, *::after { box-sizing: border-box; }
html, body { height: 100%; }
body {
  font-family: var(--mono);
  font-size: 14px;
  line-height: 1.55;
  color: var(--text);
  background: var(--bg);
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}
a { color: inherit; text-decoration: none; }
button { font-family: inherit; cursor: pointer; }
ul { list-style: none; }

/* ========== ATMOSPHERIC LAYERS (global, inherited by all routes) ========== */
/* These are applied via <div class="bg-atmosphere">...</div> placed in App.vue. */
.bg-atmosphere {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
}
.bg-deep {
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 1400px 800px at 80% -5%, rgba(255, 97, 0, 0.22), transparent 50%),
    radial-gradient(ellipse 1000px 700px at 10% 100%, rgba(193, 245, 39, 0.08), transparent 55%),
    linear-gradient(180deg, #050507 0%, #000003 100%);
}
.bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255, 97, 0, 0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 97, 0, 0.04) 1px, transparent 1px);
  background-size: 64px 64px;
  -webkit-mask-image: radial-gradient(ellipse 1000px 700px at 50% 30%, black 30%, transparent 75%);
  mask-image: radial-gradient(ellipse 1000px 700px at 50% 30%, black 30%, transparent 75%);
}
.bg-hex {
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='84' height='72' viewBox='0 0 84 72'><path d='M21 2 L63 2 L82 37 L63 70 L21 70 L2 37 Z' fill='none' stroke='%23ff6100' stroke-width='1' opacity='0.05'/></svg>");
  background-size: 84px 72px;
  opacity: 0.6;
}
.bg-scanlines {
  position: fixed; inset: 0;
  z-index: 999;
  pointer-events: none;
  background: repeating-linear-gradient(
    0deg,
    rgba(0, 0, 0, 0.12) 0px,
    rgba(0, 0, 0, 0.12) 1px,
    transparent 1px,
    transparent 3px
  );
  mix-blend-mode: multiply;
}
.bg-crt-vignette {
  position: fixed; inset: 0;
  z-index: 998;
  pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 40%, rgba(0, 0, 0, 0.6) 100%);
}
.bg-flicker {
  position: fixed; inset: 0;
  z-index: 997;
  pointer-events: none;
  background: rgba(255, 97, 0, 0.015);
  animation: bg-flicker 7s infinite;
}
@keyframes bg-flicker {
  0%, 100% { opacity: 0; }
  50% { opacity: 1; }
  52% { opacity: 0; }
  54% { opacity: 1; }
}

/* Motion preferences */
@media (prefers-reduced-motion: reduce) {
  .bg-flicker { animation: none; }
}
```

**Verificar:** `npm run dev` sin errores de SASS. Los selectores nuevos no deben chocar con nada existente (son todos `.bg-*` nuevos).

**Commit:** `feat: add global design tokens and atmosphere layer styles`

---

### Step 3 — Montar las capas atmosféricas en App.vue

**Archivo:** `resources/web/js/App.vue`

**Estado actual esperado:** un `<template>` con solo `<router-view />` y probablemente nada en `<script>` salvo export default.

**Acción:** Reemplazar el `<template>` completo por:

```vue
<template>
  <div class="app-root">
    <!-- Atmospheric layers (z-index 0, behind app) -->
    <div class="bg-atmosphere">
      <div class="bg-deep"></div>
      <div class="bg-hex"></div>
      <div class="bg-grid"></div>
    </div>

    <!-- App content (above atmosphere) -->
    <router-view class="app-content" />

    <!-- CRT overlays (z-index 997-999, above everything) -->
    <div class="bg-crt-vignette"></div>
    <div class="bg-scanlines"></div>
    <div class="bg-flicker"></div>
  </div>
</template>

<script>
export default {
  name: 'App'
};
</script>

<style lang="scss" scoped>
.app-root {
  position: relative;
  min-height: 100vh;
}
.app-content {
  position: relative;
  z-index: 1;
}
</style>
```

**Verificar:**
- `npm run dev`, abrir `/login`, `/dashboard`, `/trophy-room` → fondo debe tener hex pattern tenue, radial naranja arriba-derecha, scanlines horizontales sutiles, vignette en los bordes, flicker muy suave cada 7s.
- El contenido de las páginas (sidebar, header viejo, cards) debe quedar visible ENCIMA de la atmósfera. Si algo se ve tapado, hay un `z-index` de otra página que hay que revisar — PERO en esta phase NO tocar; anotar y avisar.
- Inspeccionar con DevTools: `.bg-atmosphere` debe tener `position:fixed`, las tres capas internas deben renderizarse.

**Commit:** `feat: mount global atmosphere layers in App.vue`

---

### Step 4 — Rediseñar main-header.vue (sticky + currencies + VT323)

**Archivo:** `resources/web/js/components/main-header.vue`

**Contexto:** El main-header actual tiene solo bell + avatar (las currencies fueron removidas en una phase anterior). Ahora vuelven, con el estilo definitivo.

**Acción:** Reemplazar el componente completo por el siguiente. Mantener imports de Vuex y la lógica existente de notificaciones/avatar si ya estaban; si no estaban, el template abajo funciona con data mock que después se reemplaza por Vuex en la phase de wiring.

**IMPORTANTE:** Antes de reemplazar, leer el archivo actual y conservar:
- El binding a Vuex para currencies (si existe `this.$store.state.user.ambar` o similar — reutilizar).
- El binding al avatar del usuario.
- El binding a notificaciones no leídas (para mostrar/ocultar `.bell-dot`).
- El handler de click en el avatar (si abre menú de perfil).

Si alguna de esas conexiones no existe todavía, dejar `computed` con un comentario `// TODO: wire to Vuex in Phase 9M` y usar valores placeholder (0 para currencies, 'U' para avatar, false para hasNotifications).

```vue
<template>
  <header class="main-header">
    <div class="header-left">
      <div class="breadcrumb">
        <span>TrophyRoom</span>
        <span class="breadcrumb-dot"></span>
        <span class="breadcrumb-current">{{ currentPageLabel }}</span>
      </div>
    </div>

    <div class="header-right">
      <div class="wallet-rail">
        <div class="coin" title="Ambar">
          <span class="coin-dot coin-dot--ambar"></span>
          <div class="coin-meta">
            <div class="coin-val">{{ formatNumber(ambar) }}</div>
            <div class="coin-lbl">Ambar</div>
          </div>
        </div>
        <div class="coin" title="Uru">
          <span class="coin-dot coin-dot--uru"></span>
          <div class="coin-meta">
            <div class="coin-val">{{ formatNumber(uru) }}</div>
            <div class="coin-lbl">Uru</div>
          </div>
        </div>
      </div>

      <button class="bell-btn" @click="onBellClick" aria-label="Notifications">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span v-if="hasNotifications" class="bell-dot"></span>
      </button>

      <router-link :to="{ name: 'profile' }" class="avatar-pill">
        <span class="avatar-img">{{ avatarInitial }}</span>
        <span class="avatar-name">{{ username }}</span>
      </router-link>
    </div>
  </header>
</template>

<script>
export default {
  name: 'MainHeader',
  computed: {
    // TODO: wire to Vuex in Phase 9M — ensure these read from the existing user store
    ambar() {
      return this.$store?.state?.user?.ambar ?? 0;
    },
    uru() {
      return this.$store?.state?.user?.uru ?? 0;
    },
    username() {
      return this.$store?.state?.user?.username ?? 'User';
    },
    avatarInitial() {
      return (this.username || 'U').charAt(0).toUpperCase();
    },
    hasNotifications() {
      return !!(this.$store?.state?.notifications?.unread);
    },
    currentPageLabel() {
      const name = this.$route?.name || '';
      const map = {
        dashboard: 'Dashboard',
        'trophy-room': 'Trophy Room',
        forge: 'Forge',
        feed: 'Achievements',
        rewards: 'Rewards',
        exchange: 'Exchange',
        network: 'Network',
        profile: 'Settings',
        'brand-dashboard': 'Admin Panel'
      };
      return map[name] || name.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
  },
  methods: {
    formatNumber(n) {
      const num = Number(n) || 0;
      return num.toLocaleString('en-US');
    },
    onBellClick() {
      // Preserve existing behavior if there was one; otherwise emit for parent.
      this.$emit('bell-click');
    }
  }
};
</script>

<style lang="scss" scoped>
.main-header {
  position: sticky;
  top: 0;
  z-index: 40;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 14px 48px;
  background: rgba(0, 0, 3, 0.7);
  backdrop-filter: blur(14px) saturate(1.3);
  -webkit-backdrop-filter: blur(14px) saturate(1.3);
  border-bottom: 1px solid rgba(255, 97, 0, 0.12);
}

.header-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.header-right { display: flex; align-items: center; gap: 12px; }

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 10px;
  color: var(--text-dim);
  letter-spacing: 0.2em;
  text-transform: uppercase;
  white-space: nowrap;
}
.breadcrumb-dot {
  width: 4px;
  height: 4px;
  background: var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--primary);
}
.breadcrumb-current { color: var(--primary); }

.wallet-rail {
  display: flex;
  gap: 10px;
  align-items: center;
}
.coin {
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 8px 14px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
}
.coin-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.coin-dot--ambar {
  background: var(--primary);
  box-shadow: 0 0 8px var(--primary);
}
.coin-dot--uru {
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
}
.coin-meta { line-height: 1; }
.coin-val {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text);
  line-height: 1;
  letter-spacing: 0.04em;
}
.coin-lbl {
  font-size: 9px;
  color: var(--text-muted);
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-top: 2px;
}

.bell-btn {
  width: 36px;
  height: 36px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  position: relative;
  transition: all 0.15s;
}
.bell-btn:hover {
  color: var(--primary);
  border-color: var(--primary);
}
.bell-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 7px;
  height: 7px;
  background: var(--primary);
  border-radius: 50%;
  box-shadow: 0 0 8px var(--primary);
  border: 1.5px solid var(--bg);
}

.avatar-pill {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 13px 4px 4px;
  border: 1px solid rgba(255, 97, 0, 0.15);
  background: rgba(14, 15, 17, 0.65);
  transition: border-color 0.15s;
}
.avatar-pill:hover { border-color: var(--primary); }
.avatar-img {
  width: 28px;
  height: 28px;
  background: linear-gradient(135deg, #f5c547, #d98c3a);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: var(--bg);
  font-weight: bold;
  flex-shrink: 0;
}
.avatar-name {
  font-size: 12px;
  color: var(--text);
  letter-spacing: 0.05em;
}

@media (max-width: 768px) {
  .main-header { padding: 12px 20px; gap: 10px; flex-wrap: wrap; }
  .wallet-rail { gap: 6px; }
  .coin { padding: 6px 10px; }
  .coin-val { font-size: 18px; }
  .avatar-name { display: none; }
}
</style>
```

**Verificar:**
- `npm run dev`. Loguearse y entrar a cualquier ruta autenticada.
- El header debe estar sticky (scroll la page, el header se queda arriba).
- Debe mostrar breadcrumb a la izquierda, wallet-rail + bell + avatar a la derecha.
- Las currencies deben mostrarse en VT323 (tipografía más ancha y pixelada que Share Tech Mono). Si las ves igual que antes, VT323 no cargó — volver al Step 1.
- El backdrop-filter debe mostrar transparencia con blur sobre la atmósfera de fondo (si se ve sólido negro, el z-index de otra capa está tapando).
- Breadcrumb debe decir "TrophyRoom • [Current Page]" con el nombre de la ruta.

**Commit:** `feat: redesign main-header with sticky sticky currencies wallet-rail`

---

### Step 5 — Wiring del main-header en Main.vue

**Archivo:** `resources/web/js/Main.vue`

**Acción:** Solo verificar que `<main-header />` ya esté renderizado en el template. Si está, no tocar. Si NO está (porque se había removido en una phase anterior), agregarlo en el layout de la siguiente forma — dentro del área `<main>`, por encima de `<router-view>`:

```vue
<template>
  <div class="main-layout">
    <sidebar />
    <div class="main-content">
      <main-header />
      <router-view />
    </div>
  </div>
</template>
```

**NO modificar** la estructura existente del sidebar ni del layout grid/flex — solo garantizar que `<main-header />` esté presente. Si ya lo estaba con un nombre distinto (ej. `<the-header />`), conservar ese uso.

**Verificar:** Login → `/dashboard`. El header aparece arriba del content de la página. Sidebar a la izquierda sigue siendo el sidebar viejo (eso se redisña en 9B).

**Commit:** `fix: ensure main-header is mounted in Main.vue layout`

---

### Step 6 — Build de producción local

**Acción:** Correr una vez:

```bash
npm run build
```

**Verificar:** Build termina sin errores. Los archivos en `public/build/` deben haberse regenerado. Hacer `git status` → los cambios en `public/build/` deben aparecer como modificados (SIEMPRE commitear public/build según el working guide).

**Commit:** `build: rebuild assets for Phase 9A`

---

### Step 7 — Deploy

Ejecutar exactamente (según working guide):

```bash
cd ~/Documents/trophyroom && git push origin main && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build"
```

Después verificar hashes:

```bash
cd ~/Documents/trophyroom && git log --oneline -1 && ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git log --oneline -1"
```

Ambos hashes deben coincidir. Si no → reintentar push + reset.

---

## Verification checklist (lo que Max va a chequear)

Abrir `https://app.ambar.gg` con DevTools → Network → "Inhabilitar la memoria caché" tildado. Hard refresh.

### Visual
- [ ] En **`/login`** (sin estar logueado): hex pattern visible de fondo, radial naranja arriba-derecha, scanlines sutiles, vignette en bordes, flicker cada ~7s muy suave.
- [ ] En **`/dashboard`** (logueado): misma atmósfera + header sticky arriba con breadcrumb izquierda y wallet-rail + bell + avatar derecha.
- [ ] Scroll en Dashboard → el header se queda arriba, las capas atmosféricas NO se mueven con el scroll (son `position:fixed`).
- [ ] Las currencies (Ambar/Uru) se ven en **VT323** (pixelada, ancha). Los labels "Ambar"/"Uru" abajo en Share Tech Mono 9px uppercase.
- [ ] El bell-dot solo aparece si hay notificaciones sin leer (si el store no está wireado todavía, no aparece — correcto).
- [ ] Avatar-pill muestra inicial del username y el username; click lleva a `/profile`.

### Funcional (no debe romperse nada)
- [ ] Navegación sidebar sigue funcionando (Dashboard, Trophy Room, Forge, etc.). El sidebar visual sigue siendo el viejo.
- [ ] Breadcrumb en el header cambia con la ruta ("TrophyRoom • Trophy Room" en `/trophy-room`, etc.).
- [ ] Logout/login siguen funcionando.
- [ ] Abrir Trophy Room, Forge, Feed — ninguna page debe estar visualmente rota. Todavía NO están rediseñadas (esas son 9D+), pero deben renderizarse sin errores de consola.
- [ ] DevTools → Console: sin errores nuevos relacionados a fonts, scss, o undefined en el header.

### Mobile (DevTools responsive 375px)
- [ ] Header se adapta: padding reducido, coins más chicas, avatar-name oculto, queda solo la inicial.
- [ ] Atmósfera sigue visible sin problemas de performance.

### Performance
- [ ] DevTools → Performance → grabar 5s de scroll. Debe mantener ~60fps. Si el flicker tira el framerate (improbable, es una animación de `opacity`), medirlo y avisar.

---

## Qué reportar de vuelta a claude.ai

Max va a pasar el output de Claude Code al chat. Lo que quiero ver:

1. Los 7 commits creados (hashes + mensajes).
2. Confirmación de que `git log` local y server coinciden.
3. Capturas de `/login` y `/dashboard` post-deploy.
4. Screenshots del header sticky en scroll (desktop + mobile 375px).
5. Cualquier error de consola o warning de Vite/SASS que haya aparecido.
6. Si algún paso falló o necesitó ajuste sobre lo que decía el brief, escribir exactamente qué y por qué.

---

## Rollback plan

Si algo rompe catastróficamente en producción (sitio inusable):

```bash
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git reset --hard HEAD~7 && npm run build"
```

El `HEAD~7` asume 7 commits creados en esta phase. Ajustar al número real de commits si cambia.

---

## Notas para el supervisor (claude.ai)

- En Phase 9B se rediseña el sidebar completo + se introduce el componente Mascot. Hasta entonces el sidebar visual queda como está.
- En Phase 9B puede ser que haya que ajustar el `z-index` de algún elemento del sidebar si la atmósfera lo tapa — monitorear.
- El mapeo de `currentPageLabel` en el header asume los route names del working guide. Si hay names distintos en el router real, ajustar el objeto `map` en el computed.
- Si al correr `npm run dev` hay un `@import` de Share Tech Mono en algún SCSS que se dejó sin borrar, el navegador va a cargar la fuente dos veces (no rompe, pero es feo). Revisar y limpiar si aparece en Network.
