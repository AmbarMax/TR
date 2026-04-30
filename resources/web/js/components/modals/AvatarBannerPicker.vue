<template>
  <div class="abp">
    <div class="abp-section">
      <h3 class="abp-section-title">Pick an avatar</h3>
      <div class="abp-avatars">
        <div
          v-for="(src, idx) in avatarPresets"
          :key="`a-${idx}`"
          class="abp-avatar"
          :class="{ 'abp-avatar--selected': selectedAvatar === src }"
          @click="selectAvatar(src)"
        >
          <img :src="src" :alt="`Avatar ${idx + 1}`" />
        </div>

        <label class="abp-avatar abp-upload">
          <input
            type="file"
            accept=".jpg,.jpeg,.png"
            ref="avatarUpload"
            @change="handleAvatarUpload"
            style="display:none"
          />
          <span class="abp-upload-icon">+</span>
          <span class="abp-upload-label">Upload</span>
        </label>
      </div>
    </div>

    <div class="abp-section">
      <h3 class="abp-section-title">Pick a banner</h3>
      <div class="abp-banners">
        <div
          v-for="(src, idx) in bannerPresets"
          :key="`b-${idx}`"
          class="abp-banner"
          :class="{ 'abp-banner--selected': selectedBanner === src }"
          @click="selectBanner(src)"
        >
          <img :src="src" :alt="`Banner ${idx + 1}`" />
        </div>

        <label class="abp-banner abp-upload abp-upload--banner">
          <input
            type="file"
            accept=".jpg,.jpeg,.png"
            ref="bannerUpload"
            @change="handleBannerUpload"
            style="display:none"
          />
          <span class="abp-upload-icon">+</span>
          <span class="abp-upload-label">Upload custom</span>
        </label>
      </div>
    </div>

    <div v-if="error" class="abp-error">{{ error }}</div>

    <div class="abp-actions">
      <button
        class="abp-save"
        :disabled="!selectedAvatar || !selectedBanner || saving"
        @click="save"
      >
        {{ saving ? 'Saving...' : 'Save & continue →' }}
      </button>
    </div>
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
    /** Initial avatar to highlight as selected (optional) */
    initialAvatar:  { type: String, default: null },
    /** Initial banner to highlight as selected (optional) */
    initialBanner:  { type: String, default: null },
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
      bannerPresets: [
        '/images/banners/preset-banner-1.png',
        '/images/banners/preset-banner-2.png',
        '/images/banners/preset-banner-3.png',
        '/images/banners/preset-banner-4.png',
        '/images/banners/preset-banner-5.png',
        '/images/banners/preset-banner-6.png',
      ],
      // For presets, holds the path string; for uploads, holds an object
      // { previewUrl, file } so save() can post the File directly.
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
        // Resolve the avatar File: either the user-uploaded file or
        // a fetched preset blob.
        let avatarFile = this.pendingAvatarFile;
        if (!avatarFile) {
          avatarFile = await fetchAsFile(this.selectedAvatar, 'avatar.png');
        }

        let bannerFile = this.pendingBannerFile;
        if (!bannerFile) {
          bannerFile = await fetchAsFile(this.selectedBanner, 'banner.png');
        }

        const avatarFormData = new FormData();
        avatarFormData.append('avatar', avatarFile);
        await api.post('/api/profile/update-avatar', avatarFormData);

        const bannerFormData = new FormData();
        bannerFormData.append('background', bannerFile);
        await api.post('/api/profile/update-background', bannerFormData);

        // Mark onboarding step done
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
  display: flex;
  flex-direction: column;
  gap: 32px;
  padding: 8px 0;
}

.abp-section-title {
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 12px;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--text-dim, #7a7570);
  margin: 0 0 16px;
}

.abp-avatars {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.abp-avatar {
  position: relative;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  cursor: pointer;
  overflow: hidden;
  border: 2px solid var(--border, #2a2c2e);
  transition: all 0.15s;
  flex-shrink: 0;
}
.abp-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.abp-avatar:hover { border-color: var(--text-muted, #b8b0a8); }
.abp-avatar--selected {
  border-color: var(--primary, #ff6100);
  box-shadow: 0 0 0 2px rgba(255, 97, 0, 0.3);
}

.abp-banners {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.abp-banner {
  position: relative;
  aspect-ratio: 24 / 5;
  border-radius: 0;
  cursor: pointer;
  overflow: hidden;
  border: 2px solid var(--border, #2a2c2e);
  transition: all 0.15s;
}
.abp-banner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.abp-banner:hover { border-color: var(--text-muted, #b8b0a8); }
.abp-banner--selected {
  border-color: var(--primary, #ff6100);
  box-shadow: 0 0 0 2px rgba(255, 97, 0, 0.3);
}

.abp-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255, 97, 0, 0.04);
  border: 2px dashed rgba(255, 97, 0, 0.3) !important;
  cursor: pointer;
  color: var(--text-muted, #b8b0a8);
  text-align: center;
  gap: 4px;
}
.abp-upload:hover {
  background: rgba(255, 97, 0, 0.08);
  border-color: var(--primary, #ff6100) !important;
  color: var(--text, #feeddf);
}
.abp-upload-icon {
  font-size: 20px;
  font-weight: 300;
  line-height: 1;
}
.abp-upload-label {
  font-size: 9px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
.abp-upload--banner .abp-upload-icon { font-size: 28px; }
.abp-upload--banner .abp-upload-label { font-size: 10px; }

.abp-error {
  padding: 10px 14px;
  background: rgba(255, 50, 50, 0.1);
  border: 1px solid rgba(255, 50, 50, 0.3);
  color: #ff7070;
  font-size: 12px;
}

.abp-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}

.abp-save {
  background: var(--primary, #ff6100);
  border: none;
  color: #000;
  padding: 12px 28px;
  font-family: var(--mono, 'Share Tech Mono', monospace);
  font-size: 13px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  cursor: pointer;
  transition: background 0.15s;
}
.abp-save:hover:not(:disabled) { background: #ff7e2e; }
.abp-save:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
