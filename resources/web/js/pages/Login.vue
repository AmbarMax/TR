<template>
  <div class="auth-card">

    <!-- LEFT SIDE — narrative immersion -->
    <div class="auth-left">
      <div class="auth-left-glow"></div>
      <div class="auth-left-scanlines"></div>

      <!-- Brand -->
      <div class="auth-brand">
        <img src="../../../web/images/web/img/tr-isologo.png" alt="TrophyRoom" class="auth-logo" />
        <span class="auth-brand-name">Trophy<span class="dot">Room</span></span>
      </div>

      <!-- Constellation: T-Rex + 10 orbiting badges (all synced for login) -->
      <div class="auth-constellation">
        <div class="constellation-inner">
          <div class="constellation-rings"></div>
          <div class="mascot-halo"></div>

          <img :src="trexHero" alt="TrophyRoom mascot" class="mascot-center" />

          <div class="orbit-badge synced" style="top:0;left:50%;transform:translateX(-50%)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:12%;right:5%">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:38%;right:-8%">
            <svg width="16" height="16" viewBox="0 0 50 50" fill="currentColor"><path d="M 25 2 C 20.709 2 16.618563 3.2007813 13.226562 5.3007812 C 13.007563 5.3737813 12.629156 5.6617969 12.285156 5.9667969 C 15.330156 3.5477969 22.371734 8.3929375 24.427734 9.8359375 C 24.773734 10.078938 25.228219 10.078938 25.574219 9.8359375 C 27.630219 8.3929375 34.671797 3.5467969 37.716797 5.9667969 C 37.372797 5.6617969 36.993391 5.3737813 36.775391 5.3007812 C 33.382391 3.2007813 29.291 2 25 2 z M 11 8 C 9.403 8 8.2363281 9.3007812 8.2363281 9.3007812 C 4.4443281 13.400781 2.0507812 18.9 2.0507812 25 C 2.0507812 37.7 12.328 48 25 48 C 31.685 48 37.771891 45.1 41.962891 40.5 C 41.962891 40.5 41.464094 37.499609 38.371094 33.099609 C 34.912094 27.882609 27.905109 21.311922 25.662109 19.294922 C 25.279109 18.950922 24.708125 18.952781 24.328125 19.300781 C 22.638125 20.847781 18.277406 25.177781 17.316406 26.300781 C 15.021406 28.700781 8.6353281 36.299609 8.2363281 40.599609 C 8.2363281 40.599609 6.8386406 37.200391 9.9316406 29.400391 C 11.856641 24.714391 17.835375 17.747984 20.734375 14.708984 C 21.119375 14.305984 21.110125 13.669109 20.703125 13.287109 C 19.743125 12.388109 17.505281 10.812609 15.488281 9.5996094 C 14.092281 8.6996094 12.497 8.1 11 8 z M 38.689453 8 C 38.036453 8 33.794078 9.3428281 29.330078 13.298828 C 28.908078 13.672828 28.891203 14.325469 29.283203 14.730469 C 30.900203 16.401469 35.322656 20.681391 37.972656 24.900391 C 41.265656 30.300391 43.2605 34.6 42.0625 40.5 C 45.7545 36.4 48.050781 31 48.050781 25 C 47.950781 19 45.655281 13.500391 41.863281 9.4003906 C 41.763281 9.3003906 41.663453 9.1996094 41.564453 9.0996094 C 40.766453 8.1996094 39.587453 8 38.689453 8 z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:25%;right:0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M13.458.86 0 7.093l3.353 12.761 2.552-.313-.701-8.024.838-.373 1.447 8.202 4.361-.535-.775-8.857.83-.37 1.591 9.025 4.412-.542-.849-9.708.84-.374 1.74 9.87L24 17.318V3.5Zm.316 19.356.222 1.256L24 23.14v-4.18l-10.22 1.256Z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:5%;right:20%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:5%;left:20%">
            <svg width="16" height="16" viewBox="0 0 50 50" fill="currentColor"><path d="M36,3.99H14c-5.514,0-10,4.486-10,10v22c0,5.514,4.486,10,10,10h22c5.514,0,10-4.486,10-10v-22C46,8.476,41.514,3.99,36,3.99z M29.993,27.883c-0.305,0.156-0.68,0.156-0.985,0C23.264,24.942,20,20.314,20,16.155c0-4.786,5.26-6.596,8.771-3.347c0.413,0.383,1.044,0.383,1.457,0C33.74,9.559,39,11.369,39,16.155C39,20.314,35.736,24.942,29.993,27.883z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:25%;left:0">
            <svg width="16" height="16" viewBox="0 0 50 50" fill="currentColor"><path d="M 19.3125 4 C 19.011719 4 18.707031 3.988281 18.40625 4.1875 C 18.105469 4.386719 18 4.699219 18 5 L 18 41.59375 C 18 41.992188 18.289063 42.394531 18.6875 42.59375 L 26.6875 45 L 27 45 C 27.199219 45 27.394531 44.914063 27.59375 44.8125 C 27.894531 44.613281 28 44.300781 28 44 L 28 13.40625 C 28.601563 13.707031 29 14.300781 29 15 L 29 26.09375 C 29 26.394531 29.199219 26.804688 29.5 26.90625 C 29.699219 27.007813 31.199219 27.90625 34 27.90625 C 36.699219 27.90625 40 26.414063 40 19.3125 C 40 13.613281 36.8125 9.292969 31.3125 7.59375 Z M 17 26.40625 L 5.90625 30.40625 L 4.3125 31 C 1.613281 32.101563 0 33.886719 0 35.6875 C 0 39.488281 2.699219 41.6875 7.5 41.6875 C 10.101563 41.6875 13.300781 41.113281 17 39.8125 L 17 36 C 16.101563 36.300781 15.113281 36.699219 14.3125 37 C 12.710938 37.601563 11.5 37.8125 10.5 37.8125 C 9 37.8125 8.300781 37.300781 8 37 C 7.601563 36.699219 7.398438 36.3125 7.5 35.8125 C 7.601563 34.8125 8.800781 33.894531 11 33.09375 C 11.5 32.894531 14.898438 31.699219 17 31 Z M 36.5 28.90625 C 34.101563 29.007813 31.601563 29.394531 29 30.09375 L 29 34.6875 C 30.101563 34.289063 31.585938 33.800781 33.6875 33 C 38.488281 31.300781 40.492188 31.488281 41.09375 31.6875 C 42.292969 31.789063 42.800781 32.5 43 33 C 43.5 34.5 41.613281 35.1875 38.8125 36.1875 C 37.511719 36.6875 31.898438 38.6875 29 39.6875 L 29 44.3125 L 44.5 38.8125 L 45.6875 38.3125 C 47.6875 37.613281 50.199219 36.300781 50 34 C 49.898438 31.800781 47.210938 30.695313 45.3125 30.09375 C 42.511719 29.195313 39.5 28.804688 36.5 28.90625 Z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:38%;left:-8%">
            <svg width="16" height="16" viewBox="0 0 50 50" fill="currentColor"><path d="M 14.529297 4 C 10.942497 4 8.2058513 5.0534259 6.4550781 7.1035156 C 4.704305 9.1536053 4 12.041439 4 15.525391 L 4 35.525391 C 4 41.557868 8.4048675 46 14.529297 46 L 24 46 L 24 4 L 14.529297 4 z M 35.474609 4 L 25.974609 4.015625 L 25.974609 46 L 35.474609 46 C 41.613609 46 46 41.565 46 35.5 L 46 15.550781 C 46 7.9587812 42.445609 4 35.474609 4 z M 14.529297 6 L 22 6 L 22 44 L 14.529297 44 C 9.323726 44 6 40.632913 6 35.525391 L 6 15.525391 C 6 12.313342 6.6623357 9.9412541 7.9765625 8.4023438 C 9.2907893 6.8634333 11.318097 6 14.529297 6 z M 14.5 10 C 13.083334 10 11.893559 10.567256 11.126953 11.429688 C 10.360347 12.292118 10 13.402778 10 14.5 C 10 15.597222 10.360347 16.707881 11.126953 17.570312 C 11.893559 18.432744 13.083334 19 14.5 19 C 15.916666 19 17.106441 18.432744 17.873047 17.570312 C 18.639653 16.707881 19 15.597222 19 14.5 C 19 13.402778 18.639653 12.292119 17.873047 11.429688 C 17.106441 10.567255 15.916666 10 14.5 10 z M 14.5 12 C 15.416666 12 15.976893 12.307744 16.376953 12.757812 C 16.777013 13.207882 17 13.847222 17 14.5 C 17 15.152778 16.777013 15.792119 16.376953 16.242188 C 15.976893 16.692256 15.416666 17 14.5 17 C 13.583334 17 13.023107 16.692256 12.623047 16.242188 C 12.222987 15.792119 12 15.152778 12 14.5 C 12 13.847222 12.222987 13.207881 12.623047 12.757812 C 13.023107 12.307745 13.583334 12 14.5 12 z M 36 24 C 38.206 24 40 25.794 40 28 C 40 30.206 38.206 32 36 32 C 33.794 32 32 30.206 32 28 C 32 25.794 33.794 24 36 24 z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="top:12%;left:5%">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.198 18.24a.966.966 0 0 1-.194-.571v-.955s0-.571.563-.313c0 0 6.919 3.135 8.033 3.626a7.832 7.832 0 0 0 3.408.729 8.216 8.216 0 0 0 3.396-.729l8.037-3.626c.559-.258.559.313.559.313v.955a1.038 1.038 0 0 1-.198.575c-.19.258-.515.539-1.411.959-.713.337-6.23 2.818-6.995 3.17a8.008 8.008 0 0 1-3.4.729 8.336 8.336 0 0 1-3.82-.927c-1.435-.65-5.849-2.631-6.567-2.972-.9-.428-1.153-.654-1.411-.963zm1.411-5.973l6.987-3.17a7.975 7.975 0 0 1 2.164-.634v5.707c0 .396.571.697 1.236.697s1.141-.313 1.141-.697V8.479c.778.105 1.54.313 2.263.618l6.987 3.17c.579.273 1.609.761 1.609 1.538s-1.011 1.236-1.609 1.53l-6.987 3.17a8.2 8.2 0 0 1-3.396.729 7.832 7.832 0 0 1-3.408-.729l-6.987-3.17C1.011 15.042 0 14.574 0 13.801s1.03-1.264 1.609-1.534zm1.807 2.247c.77.396 1.683.396 2.453 0 .682-.396.686-1.026 0-1.419a2.705 2.705 0 0 0-2.453 0c-.68.392-.666 1.02 0 1.419zM12 7.595a3.35 3.35 0 1 1 3.349-3.351v.003c0 1.849-1.5 3.348-3.349 3.348z"/></svg>
            <span class="plat-dot"></span>
          </div>
          <div class="orbit-badge synced" style="bottom:12%;left:50%;transform:translateX(-50%)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22.018 13.298l-3.919 2.218-3.515-3.493 3.543-3.521 3.891 2.202a1.49 1.49 0 0 1 0 2.594zM1.337.924a1.486 1.486 0 0 0-.112.568v21.017c0 .217.045.419.124.6l11.155-11.087L1.337.924zm12.207 10.065l3.258-3.238L3.45.195a1.466 1.466 0 0 0-.946-.179l11.04 10.973zm0 2.067l-11 10.933c.298.036.612-.016.906-.183l13.324-7.54-3.23-3.21z"/></svg>
            <span class="plat-dot"></span>
          </div>
        </div>
      </div>

      <!-- Narrative copy -->
      <div class="auth-copy">
        <div class="auth-copy-tag">Welcome back</div>
        <h2 class="auth-copy-title">Your vault<br>is <span class="accent">waiting</span>.</h2>
        <p class="auth-copy-sub">Pick up where you left off.</p>
      </div>
    </div>

    <!-- RIGHT SIDE — form -->
    <div class="auth-right">
      <div class="form-head">
        <div class="form-head-label">Log in</div>
        <h3 class="form-head-title">Enter the room</h3>
        <p class="form-head-sub">Sign in with your preferred platform or use your TrophyRoom credentials.</p>
      </div>

      <!-- Social auth buttons -->
      <div class="social-row">
        <div class="social-btn discord" @click="loginDiscord">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>
          <span>Discord</span>
        </div>
        <div class="social-btn steam" @click="loginSteam">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 .1 5 0 11.3l6.4 2.6c.5-.4 1.2-.6 1.8-.6h.2l2.8-4.1v-.1c0-2.5 2-4.5 4.5-4.5S20.3 6.6 20.3 9.1 18.3 13.6 15.8 13.6h-.1l-4.1 2.9v.2c0 1.9-1.5 3.4-3.4 3.4-1.6 0-3-1.2-3.3-2.8L.4 15.4C1.8 20.4 6.5 24 12 24c6.6 0 12-5.4 12-12S18.6 0 12 0z"/></svg>
          <span>Steam</span>
        </div>
        <div class="social-btn overwolf" @click="loginOverwolf">
          <svg width="22" height="22" viewBox="245 0 540 565" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M778.77 290.164C779.174 290.164 651.434 213.56 651.059 211.307C648.17 195.025 644.53 168.138 626.909 154.23C611.743 142.363 509.366 93.3969 509.164 92.8862C490.314 63.2555 473.61 32.2078 459.189 0C445.494 10.719 434.356 24.5724 426.636 40.4871C418.917 56.4018 414.824 73.9507 414.673 91.7747C364.382 99.2701 317.181 121.495 278.585 155.852C378.68 131.999 407.509 153.989 407.509 153.989C374.739 160.06 343.161 171.734 314.059 188.536C271.392 212.929 252.153 242.219 252.153 242.219C252.153 242.219 349.041 213.41 423.397 241.438C388.723 246.525 356.491 262.911 331.333 288.242C485.447 308.099 561.45 447.188 593.428 559.57C605.186 359.679 509.482 283.766 509.482 283.766C537.271 285.838 572.225 308.309 594.584 341.744C662.758 333.753 700.716 355.473 700.398 355.473C699.647 355.473 737.259 343.457 763.777 330.749C761.495 327.174 751.905 314.768 746.445 307.828C747.196 307.888 768.168 306.597 778.77 290.164ZM565.899 201.694C567.401 201.303 600.795 191.54 610.328 195.295C618.994 198.69 619.167 224.345 619.167 224.345C619.167 224.345 599.379 227.229 565.899 201.694Z"/>
          </svg>
          <span>Continue with Overwolf</span>
        </div>
        <div class="social-btn google" @click="loginGoogle">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M21.35 11.1h-9.17v2.87h5.28c-.23 1.36-1.66 3.98-5.28 3.98-3.18 0-5.77-2.63-5.77-5.87s2.59-5.87 5.77-5.87c1.81 0 3.02.77 3.71 1.43l2.53-2.44C16.82 3.62 14.76 2.7 12.18 2.7 6.98 2.7 2.77 6.91 2.77 12.1s4.21 9.4 9.41 9.4c5.43 0 9.03-3.82 9.03-9.18 0-.62-.07-1.09-.16-1.22z"/></svg>
          <span>Google</span>
        </div>
      </div>

      <div class="divider">Or continue with email</div>

      <!-- LOGIN FORM — Block 1 -->
      <div v-if="!twoFactorAuth && !twoFactorReset">
        <div class="form-field">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" v-model="email" placeholder="player@example.com" />
          <span class="form-error" v-if="errorLogin">Invalid credentials</span>
        </div>

        <div class="form-field">
          <div class="form-label-row">
            <label class="form-label">Password</label>
            <router-link to="/forgot-password" class="form-forgot">Forgot?</router-link>
          </div>
          <div class="form-password-wrap">
            <input
              ref="passwordInput"
              type="password"
              class="form-input"
              v-model="password"
              placeholder="••••••••"
              @keyup.enter="signIn"
            />
            <span class="form-eye" @click="togglePassView">{{ eyeIsOpen ? 'Hide' : 'Show' }}</span>
          </div>
        </div>

        <button class="form-submit" @click="signIn">
          <span>{{ sign_in_button_text }}</span>
          <span class="arrow">→</span>
        </button>

        <div class="form-footer">
          Don't have an account? <router-link to="/sign-up">Create one →</router-link>
        </div>
      </div>

      <!-- 2FA FORM — Block 2 -->
      <div v-else-if="twoFactorAuth && !twoFactorReset">
        <div class="form-field">
          <label class="form-label">One-time code</label>
          <input
            type="text"
            class="form-input form-2fa-input"
            v-model="twoFactorCode"
            @input="formatInput"
            placeholder="000000"
            @keyup.enter="signInContinue"
          />
          <span class="form-error" v-if="twoFactorErrorStatus">{{ twoFactorErrorMessage }}</span>
        </div>

        <p class="form-helper-text">
          Don't have access? <a class="form-link" @click="disable2FA">Use recovery code</a>
        </p>

        <button class="form-submit" @click="signInContinue">
          <span>Continue</span>
          <span class="arrow">→</span>
        </button>
      </div>

      <!-- 2FA RESET CONFIRMATION — Block 3 -->
      <div v-else-if="twoFactorReset" class="tfa-reset-confirm">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="tfa-check-icon">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <h2 class="tfa-reset-title">Check your email</h2>
        <p class="tfa-reset-sub">We've sent you instructions to reset your 2FA. Follow the link in the email.</p>
        <router-link to="/login" class="form-link" @click="goToLoginPage">Back to login →</router-link>
      </div>

    </div>
  </div>
</template>

<script>
import buttonWhite from "../parts/button.vue";
import store from "../store/store.js";
import router from "../router/router.js";
import api from "../api/api.js";
import getProfileData from "../services/profile-data.js";
import { consumePendingIntent } from "../services/pending-intent.js";
import trexHero from '../../../web/images/web/img/mascot/trex-voxel-hero.png';

export default {
    components: {
        buttonWhite
    },
    data() {
        return {
            trexHero,
            sign_in_button_text: 'Sign In',
            git_hub_button_text: 'Continue with GitHub',
            git_hub_button_image: 'github',
            discord_button_text: 'Continue with Discord',
            discord_button_image: 'discord',
            steam_button_text: 'Continue with Steam',
            steam_button_image: 'steam',
            email: '',
            password: '',
            eyeIsOpen: false,
            errorLogin: false,
            twoFactorAuth: false,
            twoFactorCode: null,
            twoFactorErrorStatus: false,
            twoFactorErrorMessage: '',
            twoFactorReset: false,
        }
    },
    methods: {
        goToLoginPage() {
            this.twoFactorReset = false;
            this.twoFactorAuth = false;
        },
        closeSignIn() {
            store.state.signUpModalOpen = false;
        },
        disable2FA() {
            this.$router.push('/reset-2fa?email=' + this.email);
        },
        signIn() {
            api.post('/api/startLogin', {
                email: this.email,
                password: this.password
            }).then( response => {
                if (response.status === 202) {
                    this.twoFactorAuth = !this.twoFactorAuth;
                } else if (response.status === 200) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    localStorage.setItem('centrifugo_token', response.data.token.centrifugo_token);
                    getProfileData();
                    if (localStorage.getItem('access_token')){
                        this.checkAuth();
                        consumePendingIntent(api, router).then(consumed => {
                            if (!consumed) router.push('/dashboard');
                        });
                    }
                }
            })
            .catch(error => {
                this.errorLogin = true;
            });
        },
        signInContinue() {
            this.twoFactorErrorStatus = false;
            api.post('/api/login', {
                email: this.email,
                password: this.password,
                one_time_password: this.twoFactorCode
            }).then( response => {
                if (response.status === 200) {
                    localStorage.setItem('access_token', response.data.token.access_token);
                    localStorage.setItem('centrifugo_token', response.data.token.centrifugo_token);
                    getProfileData();
                    if (localStorage.getItem('access_token')){
                        this.checkAuth();
                        api.get('/api/badges/').then( response => {
                            if (response.status === 200) {
                                if (response.data.data.length == 0){
                                    store.state.importBudgesModalOpen = true;
                                }
                            }
                        })
                        consumePendingIntent(api, router).then(consumed => {
                            if (!consumed) router.push('/dashboard');
                        });
                    }
                }
            })
            .catch(error => {
                if ((error.response.status === 400 && error.response.data.message === 'Invalid code')) {
                    this.twoFactorErrorStatus = true;
                    this.twoFactorErrorMessage = error.response.data.message;
                } else if (error.response.status === 422 && error.response.data.message === 'The one time password field is required.') {
                    this.twoFactorErrorStatus = true;
                    this.twoFactorErrorMessage = error.response.data.message;
                }
            });
        },
        checkAuth(){
            // 2FA prompt removed — opt-in from Profile settings only
        },
        togglePassView() {
            this.eyeIsOpen = !this.eyeIsOpen;
            if (this.$refs.passwordInput.type === 'password') {
                this.$refs.passwordInput.type = 'text';
            } else {
                this.$refs.passwordInput.type = 'password';
            }
        },
        formatInput() {
            this.twoFactorErrorStatus = false;
            this.twoFactorCode = this.twoFactorCode.replace(/\D/g, '');
            if (this.twoFactorCode.length >= 6) {
                this.twoFactorCode = this.twoFactorCode.substring(0, 6);
            }
        },
        loginSteam() {
            window.location.href = '/login/steam';
        },
        loginGithub() {
            window.location.href = '/login/github';
        },
        loginGoogle() {
            window.location.href = '/login/google';
        },
        loginDiscord() {
            window.location.href = '/login/discord';
        },
        loginOverwolf() {
            window.location.href = '/login/overwolf';
        }
    }

}
</script>

<style lang="scss" scoped>
/* ═══ SPLIT CARD ═══ */
.auth-card {
  width: 100%; max-width: 1080px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  background: rgba(10,11,13,0.85);
  border: 1px solid rgba(42,44,46,0.8);
  box-shadow: 0 30px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(254,237,223,0.03);
  overflow: hidden;
  padding: 0;
  border-radius: 0;
  backdrop-filter: none;
}

/* ═══ LEFT SIDE ═══ */
.auth-left {
  position: relative;
  padding: 56px 48px;
  border-right: 1px solid rgba(42,44,46,0.6);
  background:
    linear-gradient(135deg, rgba(255,97,0,0.04), transparent 50%),
    rgba(14,15,17,0.6);
  overflow: hidden;
  min-height: 480px;
  display: flex; flex-direction: column;
}
.auth-left-glow {
  position: absolute; inset: 0; pointer-events: none;
  background: radial-gradient(ellipse 500px 400px at 50% 40%, rgba(255,97,0,0.12), transparent 60%);
}
.auth-left-scanlines {
  position: absolute; inset: 0; pointer-events: none;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0.08) 0px, rgba(0,0,0,0.08) 1px, transparent 1px, transparent 4px);
  mix-blend-mode: multiply;
}

/* Brand */
.auth-brand {
  position: relative; z-index: 2;
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 36px;
}
.auth-logo {
  width: 32px; height: 32px;
  filter: drop-shadow(0 0 8px rgba(255,97,0,0.3));
}
.auth-brand-name {
  font-family: var(--display);
  font-size: 22px;
  color: var(--text);
  letter-spacing: 0.02em;
}
.auth-brand-name .dot { color: var(--primary); }

/* Constellation */
.auth-constellation {
  position: relative; z-index: 2;
  flex: 1;
  display: flex; align-items: center; justify-content: center;
  margin: 20px 0;
}
.constellation-inner {
  position: relative;
  width: 280px; height: 280px;
}
.constellation-rings {
  position: absolute; inset: 0;
  border: 1px dashed rgba(255,97,0,0.15);
  border-radius: 50%;
  animation: slow-spin 40s linear infinite;
}
.constellation-rings::before {
  content: '';
  position: absolute; inset: 30px;
  border: 1px dashed rgba(193,245,39,0.12);
  border-radius: 50%;
  animation: slow-spin-rev 30s linear infinite;
}
@keyframes slow-spin { from { transform: rotate(0); } to { transform: rotate(360deg); } }
@keyframes slow-spin-rev { from { transform: rotate(0); } to { transform: rotate(-360deg); } }

.mascot-halo {
  position: absolute;
  top: 50%; left: 50%;
  width: 160px; height: 160px;
  transform: translate(-50%, -50%);
  background: radial-gradient(circle, rgba(255,97,0,0.25), transparent 60%);
  filter: blur(18px);
  animation: halo-pulse 3s ease-in-out infinite;
  pointer-events: none;
}
@keyframes halo-pulse {
  0%, 100% { opacity: 0.7; }
  50% { opacity: 1; }
}

.mascot-center {
  position: absolute;
  top: 50%; left: 50%;
  width: 170px; height: 170px;
  transform: translate(-50%, -50%);
  object-fit: contain;
  animation: mascot-float 4s ease-in-out infinite;
  filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
  z-index: 2;
}
@keyframes mascot-float {
  0%, 100% { transform: translate(-50%, -50%); }
  50% { transform: translate(-50%, calc(-50% - 8px)); }
}

.orbit-badge {
  position: absolute;
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  background: rgba(14,15,17,0.85);
  border: 1px solid rgba(193,245,39,0.35);
  z-index: 3;
  transition: all 0.25s;
}
.orbit-badge.synced {
  color: var(--accent);
  box-shadow: 0 0 10px rgba(193,245,39,0.25);
}
.orbit-badge.synced:hover {
  transform: scale(1.1);
  box-shadow: 0 0 16px rgba(193,245,39,0.5);
}
.orbit-badge .plat-dot {
  position: absolute;
  top: -3px; right: -3px;
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 6px var(--accent);
}

/* Narrative copy */
.auth-copy {
  position: relative; z-index: 2;
  margin-top: auto;
}
.auth-copy-tag {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 16px;
  display: inline-flex; align-items: center; gap: 10px;
}
.auth-copy-tag::before {
  content: ''; width: 28px; height: 1px; background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.auth-copy-title {
  font-family: var(--display);
  font-size: 48px; line-height: 0.95;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 14px;
  text-shadow: 0 0 30px rgba(255,97,0,0.18);
}
.auth-copy-title .accent {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}
.auth-copy-sub {
  font-size: 13px; color: var(--text-muted);
  letter-spacing: 0.04em; line-height: 1.6;
  max-width: 360px;
}

/* ═══ RIGHT SIDE (FORM) ═══ */
.auth-right {
  padding: 56px 48px;
  display: flex; flex-direction: column;
  justify-content: center;
}

.form-head {
  margin-bottom: 28px;
}
.form-head-label {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 12px;
  display: inline-flex; align-items: center; gap: 10px;
}
.form-head-label::before {
  content: ''; width: 20px; height: 1px; background: var(--primary);
}
.form-head-title {
  font-family: var(--display);
  font-size: 36px; line-height: 1;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 8px;
}
.form-head-sub {
  font-size: 12px; color: var(--text-muted);
  letter-spacing: 0.03em; line-height: 1.6;
}

/* Social row */
.social-row {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
  margin-bottom: 20px;
}
.social-btn {
  padding: 12px 10px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center; gap: 8px;
  font-family: var(--mono); font-size: 11px;
  letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--text-muted);
  cursor: pointer;
  transition: all 0.15s;
}
.social-btn:hover {
  background: rgba(255,97,0,0.04);
  border-color: var(--primary);
  color: var(--text);
}
.social-btn.discord:hover { color: #5865F2; border-color: #5865F2; }
.social-btn.steam:hover   { color: #66c0f4; border-color: #66c0f4; }
.social-btn.github:hover  { color: var(--text); border-color: var(--text); }
.social-btn.google:hover  { color: #4285F4; border-color: #4285F4; }
.social-btn.overwolf:hover { color: #ff7e2e; border-color: #ff7e2e; }

.divider {
  display: flex; align-items: center; gap: 12px;
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.18em; text-transform: uppercase;
  margin: 18px 0;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px;
  background: linear-gradient(90deg, transparent, var(--border), transparent);
}

/* Form fields */
.form-field { margin-bottom: 14px; }
.form-label {
  display: block; font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.18em; text-transform: uppercase;
  margin-bottom: 6px;
}
.form-label-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 6px;
}
.form-label-row .form-label { margin-bottom: 0; }
.form-forgot {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.14em; text-transform: uppercase;
  text-decoration: none;
  transition: opacity 0.15s;
}
.form-forgot:hover { opacity: 0.7; }

.form-input {
  width: 100%; padding: 12px 14px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono);
  font-size: 13px; letter-spacing: 0.02em;
  transition: border-color 0.15s, background 0.15s;
  box-sizing: border-box;
}
.form-input:focus {
  outline: none; border-color: var(--primary);
  background: rgba(255,97,0,0.03);
  box-shadow: 0 0 0 1px rgba(255,97,0,0.15);
}
.form-input::placeholder { color: var(--text-dim); }
.form-error {
  display: block;
  font-size: 11px; color: #e24b4a;
  letter-spacing: 0.02em;
  margin-top: 6px;
}
.form-2fa-input {
  text-align: center;
  font-family: var(--display);
  font-size: 28px;
  letter-spacing: 0.4em;
}

.form-password-wrap { position: relative; }
.form-password-wrap .form-input { padding-right: 60px; }
.form-eye {
  position: absolute; right: 14px; top: 50%;
  transform: translateY(-50%);
  font-size: 10px; letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--text-muted); cursor: pointer;
  transition: color 0.15s;
}
.form-eye:hover { color: var(--primary); }

.form-helper-text {
  font-size: 11px; color: var(--text-muted);
  margin: 4px 0 14px;
}
.form-link {
  color: var(--primary); cursor: pointer;
  text-decoration: none;
}
.form-link:hover { opacity: 0.7; }

.form-submit {
  width: 100%; padding: 15px;
  background: var(--primary); color: var(--bg);
  border: 1px solid var(--primary);
  font-family: var(--mono); font-size: 12px;
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-top: 18px; cursor: pointer;
  transition: all 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  box-shadow: 0 0 20px rgba(255,97,0,0.25);
}
.form-submit:hover {
  background: #ff7e2e; border-color: #ff7e2e;
  box-shadow: 0 0 32px rgba(255,97,0,0.5);
}
.form-submit .arrow { transition: transform 0.15s; }
.form-submit:hover .arrow { transform: translateX(4px); }

.form-footer {
  margin-top: 22px; padding-top: 20px;
  border-top: 1px solid rgba(42,44,46,0.6);
  text-align: center; font-size: 12px;
  color: var(--text-muted); letter-spacing: 0.02em;
}
.form-footer a {
  color: var(--accent);
  text-decoration: none;
}

.tfa-reset-confirm {
  text-align: center;
  padding: 20px 0;
}
.tfa-check-icon {
  color: var(--accent);
  filter: drop-shadow(0 0 10px var(--accent-glow));
  margin-bottom: 20px;
}
.tfa-reset-title {
  font-family: var(--display);
  font-size: 28px;
  color: var(--text);
  letter-spacing: 0.015em;
  margin-bottom: 10px;
}
.tfa-reset-sub {
  font-size: 12px; color: var(--text-muted);
  line-height: 1.6;
  margin-bottom: 22px;
  max-width: 320px;
  margin-left: auto; margin-right: auto;
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 900px) {
  .auth-card {
    grid-template-columns: 1fr;
    max-width: 560px;
  }
  .auth-left {
    min-height: 300px;
    padding: 36px 28px;
    border-right: none;
    border-bottom: 1px solid rgba(42,44,46,0.6);
  }
  .auth-right { padding: 36px 28px; }
  .constellation-inner { width: 220px; height: 220px; }
  .mascot-center { width: 130px; height: 130px; }
  .auth-copy-title { font-size: 36px; }
  .form-head-title { font-size: 28px; }
}
@media (max-width: 500px) {
  .auth-left { padding: 28px 20px; min-height: 320px; }
  .auth-right { padding: 28px 20px; }
  .constellation-inner { width: 180px; height: 180px; }
  .mascot-center { width: 100px; height: 100px; }
  .auth-copy-title { font-size: 28px; }
  .form-head-title { font-size: 24px; }
  .social-row { grid-template-columns: 1fr; }
}
</style>
