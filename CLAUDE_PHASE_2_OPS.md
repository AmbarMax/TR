# CLAUDE_PHASE_2_OPS.md — Auth Pages Redesign

> **Operational brief for Claude Code.**
> Read `CLAUDE.md` for product context, `CLAUDE_FRONTEND_OPS.md` for design system tokens.
> This file covers Phase 2 of the UI renovation: all unauthenticated pages.

---

## Scope

Redesign ALL auth pages (Login, Signup, ForgotPassword, ResetPassword, ResetTwoFactorAuth) with the TrophyRoom 2.0 identity. The old layout (50/50 split with giant portal illustration) is replaced by a centered single-column form on a dark background with subtle hexagonal pattern.

**Files to modify:**
```
resources/web/js/Auth.vue                    ← Wrapper (currently empty — add shared layout)
resources/web/js/pages/Login.vue             ← Full rewrite of template + scoped styles
resources/web/js/pages/Signup.vue            ← Full rewrite of template + scoped styles
resources/web/js/pages/ForgotPassword.vue    ← Full rewrite of template + scoped styles
resources/web/js/pages/ResetPassword.vue     ← Full rewrite of template + scoped styles
resources/web/js/pages/ResetTwoFactorAuth.vue ← Apply same styling (keep logic)
resources/web/css/style.scss                 ← Remove/replace old .auth_* styles
```

**DO NOT modify:** `<script>` logic, API calls, Vuex interactions, router navigation, 2FA flow. Only touch `<template>` and `<style>`. The forms must keep the same `v-model` bindings, `@click` handlers, `v-if/v-else` conditionals, and component usage (`button-white`).

---

## Design Specification

### Layout Structure

```
┌─────────────────────────────────────────────┐
│  Full viewport, bg: #000003                 │
│  Hexagonal SVG pattern overlay (opacity 4%) │
│                                             │
│         ┌─────────────────┐                 │
│         │   TR logo icon  │                 │
│         │   Page title    │                 │
│         │   Tagline       │                 │
│         │                 │                 │
│         │ ┌─────────────┐ │                 │
│         │ │ Surface card │ │                 │
│         │ │ with form    │ │                 │
│         │ │ fields       │ │                 │
│         │ └─────────────┘ │                 │
│         │                 │                 │
│         │ Social login    │                 │
│         │ (Login/Signup)  │                 │
│         └─────────────────┘                 │
│                                             │
│  Subtle orange glow at bottom (opacity 3%)  │
└─────────────────────────────────────────────┘
```

- **Width:** max-width 380px, centered horizontally and vertically
- **Card:** bg `$surface` (#0e0f11), border 1px solid `$border`, border-radius 6px, padding 28px 24px
- **Inputs:** bg `$surface-2` (#1a1c1f), border 1px solid `$border`, border-radius 4px, padding 10px 12px, color `$text`, font-family `$mono`, font-size 14px
- **Input focus:** border-color `$primary` (#ff6100)
- **Labels:** color `$text-muted` (#9a9590), font-size 11px, text-transform uppercase, letter-spacing 0.12em, margin-bottom 6px
- **Primary button (Sign In / Create Account / Send Reset Link / Set New Password):** bg `$accent` (#c1f527), color `$bg` (#000003), border-radius 4px, padding 11px 0, font-size 14px, letter-spacing 0.08em, text-transform uppercase, full width
- **Button hover:** filter brightness(1.1)
- **Links:** color `$accent` (#c1f527) for navigation links (Sign up, Sign in, Back to login)
- **"Forgot?" link:** color `$primary` (#ff6100)
- **Error messages:** color #e24b4a (red), font-size 12px

### Shared Elements (go in Auth.vue)

Auth.vue should provide the full-viewport wrapper with:
1. Background color `$bg` (#000003)
2. Full viewport height (`min-height: 100vh`)
3. Flexbox centering (align-items center, justify-content center)
4. The hexagonal SVG pattern as a pseudo-element or inline SVG
5. The subtle bottom glow gradient
6. The `<router-view>` centered inside

### Hexagonal Pattern (SVG)

```html
<svg class="auth-hex-pattern" viewBox="0 0 900 900" preserveAspectRatio="xMidYMid slice">
  <defs>
    <pattern id="auth-hex" width="60" height="52" patternUnits="userSpaceOnUse">
      <polygon points="30,2 55,15 55,37 30,50 5,37 5,15" fill="none" stroke="#ff6100" stroke-width="0.8"/>
    </pattern>
  </defs>
  <rect width="100%" height="100%" fill="url(#auth-hex)"/>
</svg>
```

CSS for pattern:
```scss
.auth-hex-pattern {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0.04;
  pointer-events: none;
  z-index: 0;
}
```

Bottom glow:
```scss
.auth-glow {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 120px;
  background: linear-gradient(to top, rgba(255, 97, 0, 0.03), transparent);
  pointer-events: none;
  z-index: 0;
}
```

### Logo Block (shared across all auth pages)

Each page includes this at the top of its content (NOT in Auth.vue — because each page has different title/tagline):

```html
<div class="auth-logo-block">
  <div class="auth-logo-icon">
    <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
  </div>
  <h1 class="auth-title">PAGE TITLE HERE</h1>
  <p class="auth-tagline">Tagline here</p>
</div>
```

Styles:
```scss
.auth-logo-block {
  text-align: center;
  margin-bottom: 28px;
}

.auth-logo-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 16px;
  background: $surface;
  border: 1px solid $border;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.auth-logo-icon img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}

.auth-title {
  color: $text;
  font-family: $mono;
  font-size: 20px;
  font-weight: 400;
  letter-spacing: 0.05em;
  margin: 0;
}

.auth-tagline {
  color: $text-dim;
  font-family: $mono;
  font-size: 12px;
  letter-spacing: 0.03em;
  margin-top: 6px;
}
```

---

## Step-by-Step Execution

### Step 1: Update Auth.vue

**File:** `resources/web/js/Auth.vue`

Replace entire content with:

```vue
<template>
  <div class="auth-layout">
    <svg class="auth-hex-pattern" viewBox="0 0 900 900" preserveAspectRatio="xMidYMid slice">
      <defs>
        <pattern id="auth-hex" width="60" height="52" patternUnits="userSpaceOnUse">
          <polygon points="30,2 55,15 55,37 30,50 5,37 5,15" fill="none" stroke="#ff6100" stroke-width="0.8"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#auth-hex)"/>
    </svg>
    <div class="auth-glow"></div>
    <div class="auth-content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
export default {}
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  background-color: #000003;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.auth-hex-pattern {
  position: fixed;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0.04;
  pointer-events: none;
  z-index: 0;
}

.auth-glow {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 120px;
  background: linear-gradient(to top, rgba(255, 97, 0, 0.03), transparent);
  pointer-events: none;
  z-index: 0;
}

.auth-content {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 380px;
  padding: 40px 20px;
}
</style>
```

**Verify:** `npm run dev` → navigate to `/#/login`. You should see the old form content but now centered on a dark background with faint hex pattern. The old illustration should be gone.

---

### Step 2: Update style.scss — Remove Old Auth Styles, Add New

**File:** `resources/web/css/style.scss`

**2a. Find and REMOVE these old auth style blocks** (they control the 50/50 split layout with illustration):

Remove `.auth_wrapper` and all its children styles.
Remove `.auth_welcome_block` and all its children styles.
Remove `.auth_form_block` and all its children styles.
Remove `.greeting_block` styles.
Remove `.modal_header` styles (auth-specific — check they're not used elsewhere first).
Remove `.modal_label` styles.
Remove `.modal_input` styles (auth-specific).
Remove `.modal_password` styles.
Remove `.modal_dont_have_account` styles.
Remove `.sign_in_button` styles (auth-specific).
Remove `.separator` styles (the "or sign in with" divider).
Remove `.modal_sign_up_with_buttons` / `.modal_sign_up_with_button` styles.
Remove `.forgot_password` specific styles.
Remove `.password_input_block` styles.
Remove `.validation_error` styles.
Remove `.login-signup_link` styles.

**IMPORTANT:** Before removing, search each class name in ALL Vue files to make sure it's only used in auth pages. If a class like `.modal_input` is used elsewhere, keep it and just override in scoped styles.

**2b. Add new auth form styles** at the end of style.scss:

```scss
// ============================================
// TrophyRoom 2.0 — Auth Pages
// ============================================

.auth-logo-block {
  text-align: center;
  margin-bottom: 28px;
}

.auth-logo-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 16px;
  background: $surface;
  border: 1px solid $border;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.auth-logo-icon img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}

.auth-title {
  color: $text;
  font-family: $mono;
  font-size: 20px;
  font-weight: 400;
  letter-spacing: 0.05em;
  margin: 0;
}

.auth-tagline {
  color: $text-dim;
  font-family: $mono;
  font-size: 12px;
  letter-spacing: 0.03em;
  margin-top: 6px;
}

.auth-card {
  background: $surface;
  border: 1px solid $border;
  border-radius: 6px;
  padding: 28px 24px;
}

.auth-field {
  margin-bottom: 16px;
}

.auth-field:last-of-type {
  margin-bottom: 6px;
}

.auth-label {
  display: block;
  color: $text-muted;
  font-family: $mono;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  margin-bottom: 6px;
}

.auth-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.auth-forgot-link {
  color: $primary;
  font-family: $mono;
  font-size: 11px;
  letter-spacing: 0.05em;
  text-decoration: none;
  cursor: pointer;
}

.auth-forgot-link:hover {
  color: lighten($primary, 10%);
}

.auth-input {
  width: 100%;
  background: $surface-2;
  border: 1px solid $border;
  border-radius: 4px;
  padding: 10px 12px;
  color: $text;
  font-family: $mono;
  font-size: 14px;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
}

.auth-input:focus {
  border-color: $primary;
}

.auth-input::placeholder {
  color: $text-dim;
}

.auth-input.has-error {
  border-color: #e24b4a;
}

.auth-password-wrap {
  position: relative;
}

.auth-password-wrap .auth-input {
  padding-right: 40px;
}

.auth-password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  cursor: pointer;
  opacity: 0.4;
  transition: opacity 0.15s;
}

.auth-password-toggle:hover {
  opacity: 0.7;
}

.auth-password-toggle img,
.auth-password-toggle svg {
  width: 18px;
  height: 18px;
}

.auth-error {
  color: #e24b4a;
  font-family: $mono;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

.auth-submit {
  width: 100%;
  margin-top: 24px;
}

// Override the button-white component styles when inside auth
.auth-submit .main_button {
  background: $accent !important;
  color: $bg !important;
  border: none !important;
  border-radius: 4px !important;
  padding: 11px 0 !important;
  font-family: $mono !important;
  font-size: 14px !important;
  font-weight: 400 !important;
  letter-spacing: 0.08em !important;
  text-transform: uppercase !important;
  width: 100% !important;
  cursor: pointer;
  transition: filter 0.15s;
}

.auth-submit .main_button:hover {
  filter: brightness(1.1);
}

.auth-footer {
  text-align: center;
  margin-top: 18px;
  font-family: $mono;
  font-size: 13px;
}

.auth-footer span {
  color: $text-dim;
}

.auth-footer a,
.auth-footer .auth-link {
  color: $accent;
  text-decoration: none;
  cursor: pointer;
}

.auth-footer a:hover,
.auth-footer .auth-link:hover {
  text-decoration: underline;
}

.auth-social {
  margin-top: 20px;
}

.auth-social-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.auth-social-divider-line {
  flex: 1;
  height: 1px;
  background: $border;
}

.auth-social-divider-text {
  color: $text-dim;
  font-family: $mono;
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  white-space: nowrap;
}

.auth-social-buttons {
  display: flex;
  gap: 10px;
}

.auth-social-btn {
  flex: 1;
  background: $surface;
  border: 1px solid $border;
  border-radius: 4px;
  padding: 10px 0;
  text-align: center;
  color: $text-muted;
  font-family: $mono;
  font-size: 12px;
  letter-spacing: 0.05em;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.auth-social-btn:hover {
  border-color: $text-muted;
  color: $text;
}

// Success state (used in ForgotPassword and ResetPassword)
.auth-success-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: rgba($accent, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-success-message {
  color: $text-muted;
  font-family: $mono;
  font-size: 14px;
  line-height: 1.6;
  text-align: center;
  margin-top: 8px;
}

// 2FA code input
.auth-2fa-input {
  text-align: center;
  font-size: 24px;
  letter-spacing: 0.3em;
  padding: 12px;
}

.auth-2fa-help {
  color: $text-dim;
  font-family: $mono;
  font-size: 13px;
  margin-top: 8px;
}

.auth-2fa-help a {
  color: $text;
  text-decoration: underline;
  cursor: pointer;
}

// Responsive
@media (max-width: 480px) {
  .auth-card {
    padding: 24px 16px;
  }

  .auth-social-buttons {
    flex-direction: column;
  }
}
```

**Verify:** `npm run dev` — page should still render (old templates still reference old classes, but Auth.vue wrapper is already providing the new background). No 500 errors.

---

### Step 3: Rewrite Login.vue Template

**File:** `resources/web/js/pages/Login.vue`

Replace the ENTIRE `<template>` block. Keep `<script>` EXACTLY as-is. Remove old `<style scoped>` if present (all styles now come from style.scss).

```vue
<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Sign in to TrophyRoom</h1>
      <p class="auth-tagline">Your achievements. One place.</p>
    </div>

    <!-- Login Form -->
    <div class="auth-card" v-if="!twoFactorAuth && !twoFactorReset">
      <div class="auth-field">
        <label class="auth-label">Username or email</label>
        <input type="email" class="auth-input" v-model="email" placeholder="player@example.com">
        <span class="auth-error" v-if="errorLogin">Invalid credentials</span>
      </div>

      <div class="auth-field">
        <div class="auth-label-row">
          <label class="auth-label" style="margin-bottom: 0;">Password</label>
          <a href="/forgot-password" class="auth-forgot-link">Forgot?</a>
        </div>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="password" ref="passwordInput">
          <span class="auth-password-toggle" @click="togglePassView">
            <img v-if="eyeIsOpen" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
      </div>

      <div class="auth-submit">
        <button-white :text="sign_in_button_text" @click="signIn"></button-white>
      </div>

      <div class="auth-footer">
        <span>Don't have an account? </span>
        <router-link to="/sign-up" class="auth-link">Sign up</router-link>
      </div>
    </div>

    <!-- 2FA Form -->
    <div class="auth-card" v-else-if="twoFactorAuth && !twoFactorReset">
      <div class="auth-field">
        <label class="auth-label">One-time code</label>
        <input type="text" class="auth-input auth-2fa-input" v-model="twoFactorCode" @input="formatInput" placeholder="000000">
        <span class="auth-error" v-if="twoFactorErrorStatus">{{ twoFactorErrorMessage }}</span>
      </div>
      <p class="auth-2fa-help">
        Don't have access? <a @click="disable2FA" href="#">Use recovery code</a>
      </p>
      <div class="auth-submit">
        <button-white :text="'Continue'" @click="signInContinue"></button-white>
      </div>
    </div>

    <!-- 2FA Reset Confirmation -->
    <div class="auth-card" v-else-if="twoFactorReset" style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">Reset link sent!</h2>
      <p class="auth-success-message">Check your email and follow the link to reset your password.</p>
      <div class="auth-footer" style="margin-top: 24px;">
        <router-link to="/login" class="auth-link" @click="goToLoginPage">Back to login</router-link>
      </div>
    </div>

    <!-- Social Login (below card) -->
    <div class="auth-social" v-if="!twoFactorAuth && !twoFactorReset">
      <div class="auth-social-divider">
        <div class="auth-social-divider-line"></div>
        <span class="auth-social-divider-text">or continue with</span>
        <div class="auth-social-divider-line"></div>
      </div>
      <div class="auth-social-buttons">
        <div class="auth-social-btn">Discord</div>
        <div class="auth-social-btn" @click="loginSteam">Steam</div>
      </div>
    </div>
  </div>
</template>
```

**IMPORTANT:** The `<script>` block stays EXACTLY as-is. Do not modify any methods, data properties, imports, or logic. Only the template and style change.

Remove any existing `<style scoped>` block from Login.vue — all styles come from style.scss now.

**Verify:** `npm run dev` → `/#/login`. Form should be centered, dark surface card, hex pattern behind, no portal illustration. Sign In button should be chartreuse. Test: type credentials → error message appears in red. Test: click eye icon → password toggles. Test: "Sign up" link → navigates to signup.

---

### Step 4: Rewrite Signup.vue Template

**File:** `resources/web/js/pages/Signup.vue`

Replace the ENTIRE `<template>` block. Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

```vue
<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-tagline">Start building your trophy case.</p>
    </div>

    <!-- Signup Form -->
    <div class="auth-card">
      <div class="auth-field">
        <label class="auth-label">Name</label>
        <input type="text" class="auth-input" v-model="name" :class="{ 'has-error': getError('name') }" placeholder="Your name">
        <span class="auth-error" v-if="getError('name')">{{ getError('name') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Username</label>
        <input type="text" class="auth-input" v-model="username" @input="filterUsername" :class="{ 'has-error': getError('username') }" placeholder="Choose a username">
        <span class="auth-error" v-if="getError('username')">{{ getError('username') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Email</label>
        <input type="email" class="auth-input" v-model="email" :class="{ 'has-error': getError('email') }" placeholder="player@example.com">
        <span class="auth-error" v-if="getError('email')">{{ getError('email') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Password</label>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="password" ref="passwordInput" :class="{ 'has-error': getError('password') }">
          <span class="auth-password-toggle" @click="togglePassView">
            <img v-if="passEyes.pass" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
        <span class="auth-error" v-if="getError('password')">{{ getError('password') }}</span>
      </div>

      <div class="auth-field">
        <label class="auth-label">Confirm password</label>
        <div class="auth-password-wrap">
          <input type="password" class="auth-input" v-model="confirm_password" ref="passwordInputConfirm" :class="{ 'has-error': getError('confirm_password') }">
          <span class="auth-password-toggle" @click="togglePassConfirmView">
            <img v-if="passEyes.confirm" src="../../../web/images/web/img/icons/eye-open.svg" alt="show">
            <img v-else src="../../../web/images/web/img/icons/eye-close.svg" alt="hide">
          </span>
        </div>
        <span class="auth-error" v-if="getError('confirm_password')">{{ getError('confirm_password') }}</span>
      </div>

      <div class="auth-submit">
        <button-white :text="create_account_button_text" @click="signUp"></button-white>
      </div>

      <div class="auth-footer">
        <span>Already have an account? </span>
        <router-link to="/login" class="auth-link">Sign in</router-link>
      </div>
    </div>

    <!-- Social Signup -->
    <div class="auth-social">
      <div class="auth-social-divider">
        <div class="auth-social-divider-line"></div>
        <span class="auth-social-divider-text">or sign up with</span>
        <div class="auth-social-divider-line"></div>
      </div>
      <div class="auth-social-buttons">
        <div class="auth-social-btn">Discord</div>
        <div class="auth-social-btn">Steam</div>
      </div>
    </div>
  </div>
</template>
```

Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

**Verify:** `npm run dev` → `/#/sign-up`. Same centered layout. Test: leave fields empty → click Create Account → validation errors appear. Test: "Sign in" link → navigates to login.

---

### Step 5: Rewrite ForgotPassword.vue Template

**File:** `resources/web/js/pages/ForgotPassword.vue`

Replace the ENTIRE `<template>` block. Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

```vue
<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Forgot password?</h1>
      <p class="auth-tagline">We'll send you a reset link.</p>
    </div>

    <!-- Email Form -->
    <div class="auth-card" v-if="!status_success">
      <div class="auth-field">
        <label class="auth-label">Email</label>
        <input type="email" class="auth-input" v-model="email" placeholder="player@example.com">
      </div>

      <div class="auth-submit">
        <button-white :text="send_reset_button" @click="sendLink"></button-white>
      </div>

      <div class="auth-footer">
        <router-link to="/login" class="auth-link">Back to login</router-link>
      </div>
    </div>

    <!-- Success State -->
    <div class="auth-card" v-else style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">Reset link sent!</h2>
      <p class="auth-success-message">Check your email and follow the link to reset your password.</p>
      <div class="auth-footer" style="margin-top: 24px;">
        <router-link to="/login" class="auth-link">Back to login</router-link>
      </div>
    </div>
  </div>
</template>
```

Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

**Verify:** `npm run dev` → `/#/forgot-password`. Centered form with one email field.

---

### Step 6: Rewrite ResetPassword.vue Template

**File:** `resources/web/js/pages/ResetPassword.vue`

Replace the ENTIRE `<template>` block. Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

```vue
<template>
  <div>
    <!-- Logo + Title -->
    <div class="auth-logo-block">
      <div class="auth-logo-icon">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" />
      </div>
      <h1 class="auth-title">Reset password</h1>
      <p class="auth-tagline">Choose a new password for your account.</p>
    </div>

    <!-- Reset Form -->
    <div class="auth-card" v-if="!status_success">
      <div class="auth-field">
        <label class="auth-label">New password</label>
        <input type="password" class="auth-input" v-model="password">
      </div>

      <div class="auth-field">
        <label class="auth-label">Confirm password</label>
        <input type="password" class="auth-input" v-model="confirm_password">
      </div>

      <div class="auth-submit">
        <button-white :text="set_new_password_button" @click="resetPassword"></button-white>
      </div>

      <div class="auth-footer">
        <router-link to="/login" class="auth-link">Back to login</router-link>
      </div>
    </div>

    <!-- Success State -->
    <div class="auth-card" v-else style="text-align: center;">
      <div class="auth-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#c1f527" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <h2 class="auth-title" style="font-size: 18px;">Password changed!</h2>
      <p class="auth-success-message">Your password has been updated. You can now sign in.</p>
      <div class="auth-submit" style="margin-top: 20px;">
        <button-white :text="'Back to login'" @click="redirectToLogin"></button-white>
      </div>
    </div>
  </div>
</template>
```

Keep `<script>` EXACTLY as-is. Remove old `<style scoped>`.

**Verify:** `npm run dev` → `/#/reset-password`. Two password fields, centered.

---

### Step 7: Update ResetTwoFactorAuth.vue

**File:** `resources/web/js/pages/ResetTwoFactorAuth.vue`

Apply the same pattern: wrap content in `auth-logo-block` + `auth-card`, use `auth-field`, `auth-label`, `auth-input`, `auth-submit`, `auth-footer` classes. Keep all `<script>` logic as-is.

View the file first to understand its current template, then apply the same class pattern.

---

### Step 8: Clean Up Old Assets

**8a.** Check if the old portal illustration images are still referenced anywhere:
```bash
grep -r "bg_1.png\|bg_2.png\|portal\|welcome_block\|greeting_block" resources/web/js/ --include="*.vue"
```

If no results, the old auth illustration assets can be left in place (they're just unused now).

**8b.** Check if old auth class names are used in non-auth pages:
```bash
grep -r "auth_wrapper\|auth_form_block\|auth_welcome_block\|modal_header\|modal_input\|modal_label\|sign_in_button\|modal_dont_have_account\|login-signup_link" resources/web/js/ --include="*.vue" | grep -v "Login.vue\|Signup.vue\|ForgotPassword.vue\|ResetPassword.vue\|ResetTwoFactorAuth.vue"
```

If any results appear, DO NOT remove those class styles from style.scss — add a comment marking them as "used elsewhere" and leave them.

---

### Step 9: Final Verification

```bash
npm run dev
```

Test each page:
- [ ] `/#/login` — centered form, hex pattern, no portal illustration, Sign In works
- [ ] `/#/sign-up` — 5 fields, validation errors display, Create Account works
- [ ] `/#/forgot-password` — email field, sends reset, shows success state
- [ ] `/#/reset-password` — 2 password fields, confirms change, shows success
- [ ] `/#/reset-2fa` — same visual style as other auth pages
- [ ] Mobile (375px) — everything stacks, card has reduced padding, social buttons stack vertically
- [ ] No console errors
- [ ] No remaining "Ambar" text on any auth page
- [ ] Eye toggle icon works on password fields
- [ ] All navigation links work (Sign up ↔ Sign in, Forgot?, Back to login)

Then:
```bash
npm run build
```

Commit:
```
feat: Phase 2 — auth pages redesign with TrophyRoom identity
```

Deploy:
```bash
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git pull origin main && npm run build"
```

---

## What This Does NOT Cover (Next Phases)

- **Virtual Hall** (`/#/virtual-hall/:username`) — Phase 3, separate brief
- **Landing/Home page** — Does not currently exist as a standalone page (catchAll redirects to login). If a public landing page is needed, it's a new page, not a renovation.
- **Social login functionality** — The Discord and Steam buttons are visual only. The `@click` handlers (`loginSteam`, `loginGithub`) are currently commented out in the script. Wiring them up is a separate backend task.

---

## Development Rules (Same as CLAUDE_FRONTEND_OPS.md Section 6)

1. One change at a time. Modify → verify with `npm run dev` → next.
2. Don't break existing functionality. API calls, Vuex, routing, auth must keep working.
3. Vue 3 Options API ONLY.
4. Bootstrap → Tailwind where touched. No new Bootstrap.
5. No backend changes.
6. Mobile-first. Test at 375px.
7. Commit messages: `feat:`, `fix:`, `refactor:` prefixes.
8. When in doubt, stop and ask.
9. Share Tech Mono is the only font.
