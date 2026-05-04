<template>
  <div class="abp">
    <header class="abp-header">
      <div class="abp-headline">
        <div class="abp-eyebrow">Step 04 of 04</div>
        <h2 class="abp-title">Make your hall <span class="abp-title-accent">yours</span></h2>
      </div>
      <p class="abp-desc">Pick a look. You can change it anytime from your Profile.</p>
    </header>

    <section class="abp-section">
      <div class="abp-section-header">
        <h3 class="abp-section-title">Pick an avatar</h3>
        <span class="abp-section-meta">{{ avatarPresets.length }} presets · custom upload</span>
      </div>
      <div class="abp-avatars">
        <button
          type="button"
          v-for="(src, idx) in avatarPresets"
          :key="`a-${idx}`"
          class="abp-avatar"
          :class="{ 'abp-avatar--selected': selectedAvatar === src }"
          @click="selectAvatar(src)"
        >
          <img :src="src" :alt="`Avatar ${idx + 1}`" />
        </button>

        <label class="abp-avatar-upload">
          <input
            type="file"
            accept=".jpg,.jpeg,.png"
            ref="avatarUpload"
            @change="handleAvatarUpload"
          />
          <span class="abp-upload-icon">+</span>
          <span class="abp-upload-label">Upload</span>
        </label>
      </div>
    </section>

    <section class="abp-section">
      <div class="abp-section-header">
        <h3 class="abp-section-title">Pick a banner</h3>
        <span class="abp-section-meta">{{ bannerPresets.length }} presets · 1920×400</span>
      </div>
      <div class="abp-banners">
        <button
          type="button"
          v-for="(src, idx) in bannerPresets"
          :key="`b-${idx}`"
          class="abp-banner"
          :class="{ 'abp-banner--selected': selectedBanner === src }"
          :style="{ backgroundImage: `url('${src}')` }"
          @click="selectBanner(src)"
          :aria-label="`Banner ${idx + 1}`"
        ></button>
      </div>
      <label class="abp-banner-upload">
        <input
          type="file"
          accept=".jpg,.jpeg,.png"
          ref="bannerUpload"
          @change="handleBannerUpload"
        />
        <span class="abp-upload-icon">+</span>
        <span class="abp-upload-label">Upload custom banner</span>
      </label>
    </section>

    <div v-if="error" class="abp-error">{{ error }}</div>

    <footer class="abp-footer">
      <div class="abp-mascot-mini">
        <div class="abp-mascot-slot">
          <img src="/images/mascot-onboarding/trex_thinking.png" alt="" class="abp-mascot-img" />
        </div>
        <div class="abp-mascot-quote">Looking sharp.</div>
      </div>

      <button
        class="abp-save"
        :disabled="!selectedAvatar || !selectedBanner || saving"
        @click="save"
      >
        <span>{{ saving ? 'Saving…' : 'Save & Continue' }}</span>
        <span class="abp-save-arrow">→</span>
      </button>
    </footer>
  </div>
</template>

<script>
import api from '../../api/api.js';

// The existing /api/profile/update-avatar and /api/profile/update-background
// endpoints both require an actual image File (validated `image|mimes:...`).
// For preset selection we fetch the static asset and re-upload it as a Blob
// so the server stores it identically to a user-uploaded file.
async function fetchAsFile(url, fileName) {
  const resp = await fetch(url);
  if (!resp.ok) throw new Error(`Failed to fetch preset ${url}: ${resp.status}`);
  const blob = await resp.blob();
  return new File([blob], fileName, { type: blob.type || 'image/png' });
}

export default {
  name: 'AvatarBannerPicker',

  props: {
    initialAvatar: { type: String, default: null },
    initialBanner: { type: String, default: null },
  },

  emits: ['saved', 'error'],

  data() {
    return {
      avatarPresets: [
        '/images/avatars/preset-avatar-1.png',
        '/images/avatars/preset-avatar-2.png',
        '/images/avatars/preset-avatar-3.png',
        '/images/avatars/preset-avatar-4.png',
        '/images/avatars/preset-avatar-5.png',
        '/images/avatars/preset-avatar-6.png',
      ],
      // Banners are .jpg now (HD assets) — was .png in older revs.
      bannerPresets: [
        '/images/banners/preset-banner-1.jpg',
        '/images/banners/preset-banner-2.jpg',
        '/images/banners/preset-banner-3.jpg',
        '/images/banners/preset-banner-4.jpg',
        '/images/banners/preset-banner-5.jpg',
        '/images/banners/preset-banner-6.jpg',
      ],
      selectedAvatar: this.initialAvatar,
      selectedBanner: this.initialBanner,
      pendingAvatarFile: null,
      pendingBannerFile: null,
      saving: false,
      error: null,
    };
  },

  methods: {
    selectAvatar(src) {
      this.selectedAvatar = src;
      this.pendingAvatarFile = null;
      this.error = null;
    },

    selectBanner(src) {
      this.selectedBanner = src;
      this.pendingBannerFile = null;
      this.error = null;
    },

    handleAvatarUpload(e) {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 5 * 1024 * 1024) {
        this.error = 'Avatar must be smaller than 5 MB';
        return;
      }
      this.pendingAvatarFile = file;
      this.selectedAvatar = URL.createObjectURL(file);
      this.error = null;
    },

    handleBannerUpload(e) {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > 10 * 1024 * 1024) {
        this.error = 'Banner must be smaller than 10 MB';
        return;
      }
      this.pendingBannerFile = file;
      this.selectedBanner = URL.createObjectURL(file);
      this.error = null;
    },

    async save() {
      if (!this.selectedAvatar || !this.selectedBanner) return;

      this.saving = true;
      this.error = null;

      try {
        let avatarFile = this.pendingAvatarFile;
        if (!avatarFile) {
          avatarFile = await fetchAsFile(this.selectedAvatar, 'avatar.png');
        }

        let bannerFile = this.pendingBannerFile;
        if (!bannerFile) {
          bannerFile = await fetchAsFile(this.selectedBanner, 'banner.jpg');
        }

        const avatarFormData = new FormData();
        avatarFormData.append('avatar', avatarFile);
        await api.post('/api/profile/update-avatar', avatarFormData);

        const bannerFormData = new FormData();
        bannerFormData.append('background', bannerFile);
        await api.post('/api/profile/update-background', bannerFormData);

        await api.post('/api/onboarding/step', { step: 'hall_personalized' });

        this.$emit('saved', {
          avatar: this.selectedAvatar,
          banner: this.selectedBanner,
        });
      } catch (err) {
        this.error = 'Save failed. Please try again.';
        this.$emit('error', err);
        console.error('AvatarBannerPicker save error:', err);
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.abp {
  width: 100%;
  background: linear-gradient(180deg, rgba(20,22,26,0.95) 0%, rgba(12,13,15,0.98) 100%);
  border: 1px solid var(--border-2, #2a2e34);
  box-shadow: 0 0 80px rgba(0,0,0,0.5);
  padding: 48px 56px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  color: var(--text, #feeddf);
}

/* Header */
.abp-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 24px;
  margin-bottom: 40px;
  padding-bottom: 24px;
  border-bottom: 1px solid var(--border, #1f2226);
}
.abp-headline { flex: 1; }
.abp-eyebrow {
  font-size: 10px;
  letter-spacing: 0.3em;
  color: var(--primary, #ff6100);
  text-transform: uppercase;
  margin-bottom: 8px;
}
.abp-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 48px;
  line-height: 1;
  color: var(--text);
  margin: 0;
}
.abp-title-accent { color: var(--primary, #ff6100); }
.abp-desc {
  font-size: 13px;
  color: var(--text-dim, #7a7570);
  max-width: 280px;
  text-align: right;
  line-height: 1.6;
  margin: 0;
}

/* Sections */
.abp-section { margin-bottom: 36px; }
.abp-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.abp-section-title {
  font-family: var(--display, 'VT323', monospace);
  font-size: 22px;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}
.abp-section-title::before {
  content: '';
  width: 6px;
  height: 6px;
  background: var(--primary, #ff6100);
  box-shadow: 0 0 8px var(--primary, #ff6100);
}
.abp-section-meta {
  font-size: 9px;
  letter-spacing: 0.25em;
  color: var(--text-dim, #7a7570);
  text-transform: uppercase;
}

/* Avatars */
.abp-avatars {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.abp-avatar {
  width: 96px;
  height: 96px;
  border: 2px solid var(--border-2, #2a2e34);
  background: var(--surface, #0c0d0f);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  padding: 0;
}
.abp-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  image-rendering: pixelated;
  image-rendering: -moz-crisp-edges;
  image-rendering: crisp-edges;
}
.abp-avatar::after {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 10px; height: 10px;
  border-top: 1px solid var(--text-dim, #7a7570);
  border-left: 1px solid var(--text-dim, #7a7570);
  pointer-events: none;
  transition: border-color 0.2s;
}
.abp-avatar:hover {
  transform: translateY(-2px) scale(1.05);
  border-color: var(--text-2, #b8b0a8);
}
.abp-avatar:hover::after { border-color: var(--text-2, #b8b0a8); }
.abp-avatar--selected {
  border-color: var(--primary, #ff6100);
  transform: scale(1.05);
  box-shadow: 0 0 0 4px rgba(255,97,0,0.2), 0 0 20px rgba(255,97,0,0.4);
}
.abp-avatar--selected::after {
  content: '✓';
  width: 24px; height: 24px;
  background: var(--primary, #ff6100);
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: bold;
  font-family: var(--mono);
  top: auto; left: auto;
  bottom: 0; right: 0;
  border: none;
  border-top: 2px solid var(--bg, #07080a);
  border-left: 2px solid var(--bg, #07080a);
}

/* Avatar upload */
.abp-avatar-upload {
  width: 96px;
  height: 96px;
  border: 2px dashed rgba(255,97,0,0.4);
  background: rgba(255,97,0,0.04);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  gap: 4px;
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text-dim, #7a7570);
  flex-shrink: 0;
}
.abp-avatar-upload:hover {
  border-color: var(--primary, #ff6100);
  color: var(--primary, #ff6100);
  background: rgba(255,97,0,0.08);
}
.abp-avatar-upload input,
.abp-banner-upload input { display: none; }
.abp-upload-icon {
  font-size: 22px;
  font-weight: 300;
  line-height: 1;
}
.abp-upload-label {
  font-size: 9px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

/* Banners */
.abp-banners {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 12px;
}
.abp-banner {
  position: relative;
  aspect-ratio: 24 / 5;
  border: 2px solid var(--border-2, #2a2e34);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
  overflow: hidden;
  background-size: cover;
  background-position: center;
  image-rendering: pixelated;
  padding: 0;
}
.abp-banner:hover {
  transform: translateY(-2px);
  border-color: var(--text-2, #b8b0a8);
}
.abp-banner--selected {
  border-color: var(--primary, #ff6100);
  box-shadow: 0 0 0 3px rgba(255,97,0,0.2), 0 0 30px rgba(255,97,0,0.3);
}
.abp-banner--selected::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(255,97,0,0.1) 100%);
}

/* Banner upload */
.abp-banner-upload {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  aspect-ratio: 24 / 5;
  height: auto;
  width: 100%;
  border: 2px dashed rgba(255,97,0,0.4);
  background: rgba(255,97,0,0.04);
  cursor: pointer;
  transition: all 0.2s;
  color: var(--text-dim, #7a7570);
  margin-top: 0;
  max-height: 56px;
}
.abp-banner-upload:hover {
  border-color: var(--primary, #ff6100);
  color: var(--primary, #ff6100);
  background: rgba(255,97,0,0.08);
}

/* Error */
.abp-error {
  padding: 10px 14px;
  background: rgba(255, 50, 50, 0.1);
  border: 1px solid rgba(255, 50, 50, 0.3);
  color: #ff7070;
  font-size: 12px;
  margin-bottom: 16px;
}

/* Footer */
.abp-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid var(--border, #1f2226);
}

.abp-mascot-mini {
  display: flex;
  align-items: center;
  gap: 16px;
}
.abp-mascot-slot {
  width: 64px;
  height: 64px;
  background:
    radial-gradient(circle at center, rgba(255,97,0,0.15), transparent 70%),
    repeating-linear-gradient(45deg, var(--surface-2, #14161a), var(--surface-2, #14161a) 4px, var(--surface, #0c0d0f) 4px, var(--surface, #0c0d0f) 8px);
  border: 1px dashed var(--border-2, #2a2e34);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.abp-mascot-img {
  width: 88%;
  height: 88%;
  object-fit: contain;
  image-rendering: pixelated;
}
.abp-mascot-quote {
  font-family: var(--display, 'VT323', monospace);
  font-size: 18px;
  color: var(--text-2, #b8b0a8);
  max-width: 240px;
  line-height: 1.3;
}

.abp-save {
  display: inline-flex;
  align-items: center;
  gap: 14px;
  background: var(--primary, #ff6100);
  border: none;
  color: #000;
  padding: 14px 28px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
  overflow: hidden;
}
.abp-save:hover:not(:disabled) {
  background: var(--primary-2, #ff7e2e);
  box-shadow: 0 0 24px rgba(255,97,0,0.5);
}
.abp-save:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.abp-save-arrow { transition: transform 0.2s; }
.abp-save:hover:not(:disabled) .abp-save-arrow { transform: translateX(4px); }

@media (max-width: 720px) {
  .abp { padding: 28px 20px; }
  .abp-header { flex-direction: column; align-items: flex-start; }
  .abp-desc { text-align: left; max-width: none; }
  .abp-title { font-size: 36px; }
  .abp-banners { grid-template-columns: repeat(2, 1fr); }
  .abp-footer { flex-direction: column; align-items: stretch; gap: 16px; }
  .abp-save { justify-content: center; }
}
</style>
