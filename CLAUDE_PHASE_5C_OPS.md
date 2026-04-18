# CLAUDE_PHASE_5C_OPS.md — Settings Page Redesign

> Read TROPHYROOM_WORKING_GUIDE.md and CLAUDE.md before starting.
> Execute ONE step at a time. Run `npm run dev` after each step to verify no errors.
> Vue 3 Options API ONLY. Share Tech Mono only. No Composition API, no `<script setup>`.

---

## Goal

Completely restyle Profile.vue as a Settings page with 3 sections: Profile, Virtual Hall (featured slots + social links), and Security. Remove the legacy Tokens section. Preserve ALL existing API calls and functionality. Add new Virtual Hall customization fields (mocked for now — backend later).

---

## Step 1 — Rewrite Profile.vue

**Replace the ENTIRE content of:** `resources/web/js/pages/Profile.vue`

This is a full rewrite. The new version preserves all existing methods and API calls but restructures the template and styles completely.

```vue
<template>
  <div class="settings-page">
    <div class="settings-label">Settings</div>
    <p class="settings-subtitle">Manage your profile, Virtual Hall, and security</p>

    <!-- Background + Avatar -->
    <div class="settings-bg" @click="openInputBackground">
      <img v-if="background !== null" :src="getBackground()" alt="background" class="settings-bg-img" />
      <span v-else class="settings-bg-placeholder">Click to change background</span>
      <input accept=".jpg, .jpeg, .png" name="background" ref="fileInputBackground" type="file" @change="uploadBackground" style="display: none" />
    </div>
    <div class="settings-avatar-row">
      <div class="settings-avatar" @click="openInputAvatar">
        <img v-if="avatar !== null" :src="getAvatar()" alt="avatar" class="settings-avatar-img" />
        <span v-else class="settings-avatar-placeholder">{{ getInitials(name) }}</span>
      </div>
      <span class="settings-avatar-link" @click="openInputAvatar">Change avatar</span>
      <input accept=".jpg, .jpeg, .png" name="avatar" ref="fileInput" type="file" @change="uploadAvatar" style="display: none" />
    </div>

    <!-- SECTION: Profile -->
    <div class="settings-section-label">Profile</div>

    <div class="settings-grid-2">
      <label class="settings-field">
        <span class="settings-field-label">Name</span>
        <input type="text" autocomplete="off" v-model="name" class="settings-input" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label">Username</span>
        <input type="text" autocomplete="off" v-model="username" @input="filterUsername" class="settings-input" />
      </label>
    </div>

    <label class="settings-field">
      <span class="settings-field-label">Email</span>
      <input type="email" name="email" autocomplete="off" v-model="email" class="settings-input" />
    </label>

    <label class="settings-field">
      <span class="settings-field-label">Tagline</span>
      <input type="text" autocomplete="off" v-model="description" class="settings-input" placeholder="A short bio for your Virtual Hall" />
    </label>

    <div class="settings-grid-3">
      <label class="settings-field">
        <span class="settings-field-label">Phone</span>
        <input type="tel" autocomplete="off" v-model="phone_number" class="settings-input" placeholder="Optional" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label">Date of birth</span>
        <VueDatePicker class="settings-datepicker" v-model="date_of_birth" dark auto-apply :enable-time-picker="false" :max-date="maxDate" :only-calendar="true" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label">Country</span>
        <CustomSelectCountries
          v-if="allCountries.length"
          @handleCountry="handleChildData"
          :id="country_id"
          :options="allCountries"
          class="settings-country-select"
        />
      </label>
    </div>

    <button class="settings-save-btn" @click="updateProfile">Save profile</button>

    <!-- SECTION: Virtual Hall -->
    <div class="settings-divider"></div>
    <div class="settings-section-label">Virtual Hall</div>
    <p class="settings-section-desc">Customize what appears on your public profile</p>

    <div class="settings-subsection-label">Featured (3 slots)</div>
    <div class="settings-featured-grid">
      <div v-for="(slot, index) in featuredSlots" :key="index" class="settings-featured-card" :class="{ 'settings-featured-empty': !slot.item }" @click="openFeaturedPicker(index)">
        <template v-if="slot.item">
          <div class="settings-featured-icon">
            <img v-if="slot.item.image" :src="slot.item.image" alt="featured" class="settings-featured-img" />
            <span v-else class="settings-featured-letter">{{ slot.item.type === 'trophy' ? 'T' : slot.item.type === 'badge' ? 'B' : 'A' }}</span>
          </div>
          <div class="settings-featured-name">{{ slot.item.name }}</div>
          <div class="settings-featured-type">{{ slot.item.type }}</div>
          <div class="settings-featured-change">Change</div>
        </template>
        <template v-else>
          <div class="settings-featured-icon settings-featured-icon-empty">+</div>
          <div class="settings-featured-name" style="color: #5a5550;">Add featured</div>
          <div class="settings-featured-type">Trophy, badge, or achievement</div>
        </template>
      </div>
    </div>

    <div class="settings-subsection-label">Social links</div>
    <div class="settings-grid-2">
      <label class="settings-field">
        <span class="settings-field-label-dim">Twitter / X</span>
        <input type="text" autocomplete="off" v-model="socialLinks.twitter" class="settings-input" placeholder="@username" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">Twitch</span>
        <input type="text" autocomplete="off" v-model="socialLinks.twitch" class="settings-input" placeholder="username or URL" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">YouTube</span>
        <input type="text" autocomplete="off" v-model="socialLinks.youtube" class="settings-input" placeholder="channel URL" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">Instagram</span>
        <input type="text" autocomplete="off" v-model="socialLinks.instagram" class="settings-input" placeholder="@username" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">Discord</span>
        <input type="text" autocomplete="off" v-model="socialLinks.discord" class="settings-input" placeholder="username#0000" />
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">Website</span>
        <input type="text" autocomplete="off" v-model="socialLinks.website" class="settings-input" placeholder="https://..." />
      </label>
    </div>

    <button class="settings-save-btn" @click="saveVirtualHall">Save Virtual Hall</button>

    <!-- SECTION: Security -->
    <div class="settings-divider"></div>
    <div class="settings-section-label">Security</div>

    <div class="settings-subsection-label">Change password</div>
    <div class="settings-grid-3">
      <label class="settings-field">
        <span class="settings-field-label-dim">Old password</span>
        <div class="settings-password-wrapper">
          <input :type="passEyes.old ? 'text' : 'password'" autocomplete="off" v-model="oldPassword" class="settings-input" />
          <span class="settings-eye" @click="passEyes.old = !passEyes.old">{{ passEyes.old ? 'Hide' : 'Show' }}</span>
        </div>
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">New password</span>
        <div class="settings-password-wrapper">
          <input :type="passEyes.new ? 'text' : 'password'" autocomplete="off" v-model="newPassword" class="settings-input" />
          <span class="settings-eye" @click="passEyes.new = !passEyes.new">{{ passEyes.new ? 'Hide' : 'Show' }}</span>
        </div>
      </label>
      <label class="settings-field">
        <span class="settings-field-label-dim">Confirm password</span>
        <div class="settings-password-wrapper">
          <input :type="passEyes.confirm ? 'text' : 'password'" autocomplete="off" v-model="confirmPassword" class="settings-input" />
          <span class="settings-eye" @click="passEyes.confirm = !passEyes.confirm">{{ passEyes.confirm ? 'Hide' : 'Show' }}</span>
        </div>
      </label>
    </div>
    <button class="settings-save-btn" @click="updatePassword">Update password</button>

    <div class="settings-subsection-label" style="margin-top: 24px;">Two-factor authentication</div>
    <div class="settings-2fa-card">
      <div class="settings-2fa-info">
        <div class="settings-2fa-title">2FA Status</div>
        <div class="settings-2fa-status" :class="$store.state.google2fa_status ? 'settings-2fa-active' : 'settings-2fa-inactive'">
          {{ $store.state.google2fa_status ? 'Active' : 'Inactive' }}
        </div>
      </div>
      <div class="settings-2fa-actions">
        <button v-if="!$store.state.google2fa_status" class="settings-secondary-btn" @click="activate2fa">Activate</button>
        <template v-if="$store.state.google2fa_status">
          <button class="settings-secondary-btn" @click="deactivate2fa">Deactivate</button>
          <button class="settings-secondary-btn" @click="activate2fa">Change code</button>
        </template>
      </div>
    </div>

  </div>
</template>

<script>
import buttonWhite from "../parts/button.vue";
import CustomSelectCountries from "../parts/custom-select-countries.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import api from "../api/api.js";
import getProfileData from "../services/profile-data.js";
import refreshToken from "../services/refresh-token.js";
import store from "../store/store.js";

export default {
  components: {
    CustomSelectCountries,
    buttonWhite,
    VueDatePicker,
  },
  data() {
    return {
      passEyes: {
        old: false,
        new: false,
        confirm: false,
      },
      maxDate: new Date(),
      name: null,
      avatar: null,
      background: null,
      username: null,
      email: null,
      phone_number: null,
      description: null,
      date_of_birth: "",
      country_id: 0,
      oldPassword: null,
      newPassword: null,
      confirmPassword: null,
      allCountries: [],
      featuredSlots: [
        {
          item: { name: "Ambar Founders", type: "trophy", image: null },
        },
        {
          item: { name: "MVP Test", type: "trophy", image: null },
        },
        {
          item: null,
        },
      ],
      socialLinks: {
        twitter: "",
        twitch: "",
        youtube: "",
        instagram: "",
        discord: "",
        website: "",
      },
    };
  },
  methods: {
    getInitials(name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
    },
    handleChildData(data) {
      this.country_id = data;
    },
    filterUsername() {
      this.username = this.username.replace(/\s/g, "");
    },
    updateProfile() {
      api
        .put("/api/profile/update", {
          email: this.email,
          name: this.name,
          username: this.username,
          phone_number: this.phone_number,
          date_of_birth: this.date_of_birth,
          country_id: this.country_id,
          description: this.description,
        })
        .then((response) => {
          this.profileIndex();
          if (response.status === 201) {
            store.state.userUsername = this.username;
            store.state.notification = {
              message: "Profile has been successfully updated",
              type: "success",
              show: true,
            };
          } else {
            store.state.notification = {
              message: "Profile has not been updated",
              type: "error",
              show: true,
            };
          }
        })
        .catch((error) => {
          store.state.notification = {
            message: "Profile has not been updated",
            type: "error",
            show: true,
          };
        });
    },
    updatePassword() {
      api
        .put("/api/profile/update-password", {
          old_password: this.oldPassword,
          new_password: this.newPassword,
          confirm_password: this.confirmPassword,
        })
        .then((response) => {
          this.profileIndex();
          if (response.status === 201) {
            store.state.notification = {
              message: "Password has been successfully changed",
              type: "success",
              show: true,
            };
          } else {
            store.state.notification = {
              message: "Password has not been changed",
              type: "error",
              show: true,
            };
          }
        })
        .catch((error) => {
          store.state.notification = {
            message: "Password has not been changed",
            type: "error",
            show: true,
          };
        });
    },
    getAllCountries() {
      api
        .get("/api/getAllCountries")
        .then((response) => {
          this.allCountries = response.data.countries;
          this.profileIndex();
        })
        .catch((error) => {
          console.log(error);
        });
    },
    profileIndex() {
      api
        .get("/api/profile/")
        .then((response) => {
          const user = response.data.user.data;
          if (user.avatar === "/images/avatar/default-profile-img.png") {
            store.state.userAvatar = "";
          } else {
            store.state.userAvatar = user.avatar;
          }
          store.state.userUsername = user.username;
          store.state.google2fa_status = user.google2fa_status;
          if (
            user.avatar &&
            user.avatar != "/images/avatar/default-profile-img.png"
          ) {
            this.avatar = user.avatar;
          }
          if (
            user.background &&
            user.background != "/images/background/default-background-img.png"
          ) {
            this.background = user.background;
          }
          this.name = user.name;
          this.username = user.username;
          this.email = user.email;
          this.phone_number = user.phone_number;
          this.description = user.description;
          this.date_of_birth = user.date_of_birth;
          if (user.country_id !== null) {
            this.country_id = user.country_id;
          }
          refreshToken();
          this.$store.commit("updateHeaderData");
        })
        .catch((error) => {
          console.log(error);
        });
    },
    openInputAvatar() {
      this.$refs.fileInput.click();
    },
    uploadAvatar(event) {
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append("avatar", file);
      api
        .post("/api/profile/update-avatar", formData)
        .then((response) => {
          this.profileIndex();
          if (response.status === 200) {
            store.state.notification = {
              message: "Avatar has been successfully changed",
              type: "success",
              show: true,
            };
          } else {
            store.state.notification = {
              message: "Avatar has not been changed",
              type: "error",
              show: true,
            };
          }
        })
        .catch((error) => {
          store.state.notification = {
            message: "Avatar has not been updated",
            type: "error",
            show: true,
          };
        });
    },
    openInputBackground() {
      this.$refs.fileInputBackground.click();
    },
    uploadBackground(event) {
      const file = event.target.files[0];
      const formData = new FormData();
      formData.append("background", file);
      api
        .post("/api/profile/update-background", formData)
        .then((response) => {
          this.profileIndex();
          if (response.status === 200) {
            store.state.notification = {
              message: "Background has been successfully changed",
              type: "success",
              show: true,
            };
          } else {
            store.state.notification = {
              message: "Background has not been changed",
              type: "error",
              show: true,
            };
          }
        })
        .catch((error) => {
          store.state.notification = {
            message: "Background has not been changed",
            type: "error",
            show: true,
          };
        });
    },
    getAvatar() {
      return this.avatar;
    },
    getBackground() {
      return this.background;
    },
    activate2fa() {
      api
        .post("/api/2fa-get")
        .then((response) => {
          if (response.status === 200) {
            store.state.modals.twoFactorAuthModal.show = true;
            store.state.modals.twoFactorAuthModal.data = response.data;
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    deactivate2fa() {
      api
        .post("/api/2fa-deactivate")
        .then((response) => {
          if (response.status === 201) {
            store.state.notification = {
              message: response.data.message,
              type: "success",
              show: true,
            };
            getProfileData();
          }
        })
        .catch((error) => {
          console.log(error);
        });
    },
    openFeaturedPicker(index) {
      // TODO: open modal to pick from user's trophies/badges/achievements
      console.log("Open featured picker for slot", index);
      store.state.notification = {
        message: "Featured picker coming soon (backend needed)",
        type: "success",
        show: true,
      };
    },
    saveVirtualHall() {
      // TODO: backend integration — POST social links + featured slots
      console.log("Save Virtual Hall:", this.socialLinks, this.featuredSlots);
      store.state.notification = {
        message: "Virtual Hall saved! (mock — backend not connected yet)",
        type: "success",
        show: true,
      };
    },
  },
  mounted() {
    this.getAllCountries();
  },
};
</script>

<style scoped>
.settings-page {
  padding-top: 20px;
  padding-left: 32px;
  max-width: 680px;
  padding-bottom: 80px;
}
.settings-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #ff6100;
  margin-bottom: 6px;
}
.settings-subtitle {
  font-family: "Share Tech Mono", monospace;
  font-size: 12px;
  color: #9a9590;
  margin: 0 0 24px 0;
}

/* Background + Avatar */
.settings-bg {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  position: relative;
  margin-bottom: -24px;
}
.settings-bg-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.settings-bg-placeholder {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
}
.settings-avatar-row {
  display: flex;
  align-items: flex-end;
  gap: 16px;
  padding-left: 16px;
  margin-bottom: 24px;
}
.settings-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 3px solid #000003;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  z-index: 1;
  overflow: hidden;
  flex-shrink: 0;
}
.settings-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.settings-avatar-placeholder {
  font-family: "Share Tech Mono", monospace;
  font-size: 18px;
  color: #9a9590;
}
.settings-avatar-link {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  cursor: pointer;
  padding-bottom: 4px;
}
.settings-avatar-link:hover {
  color: #ff6100;
}

/* Section labels */
.settings-section-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #ff6100;
  margin-bottom: 14px;
}
.settings-section-desc {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  margin: -8px 0 16px 0;
}
.settings-subsection-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #9a9590;
  margin-bottom: 10px;
}
.settings-divider {
  border-top: 1px solid #2a2c2e;
  margin: 32px 0 24px 0;
}

/* Form fields */
.settings-field {
  display: block;
  margin-bottom: 12px;
}
.settings-field-label {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #9a9590;
  display: block;
  margin-bottom: 6px;
}
.settings-field-label-dim {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #5a5550;
  display: block;
  margin-bottom: 6px;
}
.settings-input {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 14px;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
.settings-input::placeholder {
  color: #5a5550;
}
.settings-input:focus {
  border-color: #ff6100;
}
.settings-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.settings-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
}

/* Save buttons */
.settings-save-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #000003;
  background: #c1f527;
  border: none;
  padding: 6px 20px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 4px;
  transition: opacity 0.2s;
}
.settings-save-btn:hover {
  opacity: 0.85;
}

/* Featured slots */
.settings-featured-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}
.settings-featured-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s;
}
.settings-featured-card:hover {
  border-color: rgba(255, 97, 0, 0.3);
}
.settings-featured-empty {
  border-style: dashed;
}
.settings-featured-icon {
  width: 48px;
  height: 48px;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 8px;
  margin: 0 auto 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.settings-featured-icon-empty {
  border-style: dashed;
  font-family: "Share Tech Mono", monospace;
  font-size: 18px;
  color: #5a5550;
}
.settings-featured-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.settings-featured-letter {
  font-family: "Share Tech Mono", monospace;
  font-size: 14px;
  color: #5a5550;
}
.settings-featured-name {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #feeddf;
  margin-bottom: 4px;
}
.settings-featured-type {
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  color: #5a5550;
}
.settings-featured-change {
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  color: #ff6100;
  margin-top: 6px;
}

/* Password */
.settings-password-wrapper {
  position: relative;
}
.settings-password-wrapper .settings-input {
  padding-right: 50px;
}
.settings-eye {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-family: "Share Tech Mono", monospace;
  font-size: 10px;
  color: #5a5550;
  cursor: pointer;
}
.settings-eye:hover {
  color: #ff6100;
}

/* 2FA */
.settings-2fa-card {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.settings-2fa-title {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
}
.settings-2fa-status {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  margin-top: 2px;
}
.settings-2fa-active {
  color: #c1f527;
}
.settings-2fa-inactive {
  color: #9a9590;
}
.settings-2fa-actions {
  display: flex;
  gap: 8px;
}
.settings-secondary-btn {
  font-family: "Share Tech Mono", monospace;
  font-size: 11px;
  color: #9a9590;
  background: transparent;
  border: 1px solid #2a2c2e;
  padding: 5px 14px;
  border-radius: 4px;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
.settings-secondary-btn:hover {
  border-color: #5a5550;
  color: #feeddf;
}

/* Country select overrides */
.settings-country-select {
  height: auto;
}
.settings-country-select input {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 10px 14px;
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  outline: none;
  box-sizing: border-box;
}
.settings-country-select input:focus {
  border-color: #ff6100;
}
.settings-country-select .items {
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  margin-top: 4px;
}
.settings-country-select .items div {
  font-family: "Share Tech Mono", monospace;
  font-size: 13px;
  color: #feeddf;
  padding: 8px 14px;
}
.settings-country-select .items div:hover {
  background: #252729;
}

/* Datepicker overrides */
.settings-datepicker {
  width: 100%;
}
.settings-datepicker .dp__input {
  background: #1a1c1f !important;
  border: 1px solid #2a2c2e !important;
  border-radius: 6px !important;
  padding: 10px 14px !important;
  font-family: "Share Tech Mono", monospace !important;
  font-size: 13px !important;
  color: #feeddf !important;
}
.settings-datepicker .dp__input:focus {
  border-color: #ff6100 !important;
}

/* Responsive */
@media (max-width: 968px) {
  .settings-page {
    padding-left: 16px;
    padding-right: 16px;
  }
  .settings-grid-3 {
    grid-template-columns: 1fr;
  }
  .settings-featured-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 640px) {
  .settings-grid-2 {
    grid-template-columns: 1fr;
  }
  .settings-avatar-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  .settings-2fa-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
}
</style>
```

**After replacing, run:** `npm run dev`

---

## Step 2 — Update router: rename route to 'settings'

**Edit file:** `resources/web/js/router/routes.js`

Find the profile route:

```js
            {
                path: '/profile',
                component: Profile,
                name: 'profile'
            },
```

Replace with:

```js
            {
                path: '/profile',
                component: Profile,
                name: 'settings'
            },
```

This keeps the `/profile` URL but renames the route internally. No other changes needed — the import stays the same.

**After editing, run:** `npm run dev`

---

## Step 3 — Verify and commit

Run these checks:

1. `npm run dev` — should compile with zero errors
2. Navigate to `/profile` — should show the new Settings page with 3 sections
3. Profile section: all fields should load data (name, username, email, etc.)
4. Avatar/background upload buttons should work (click triggers file picker)
5. Virtual Hall section: 3 featured slots visible, social link inputs visible
6. Security section: password fields with Show/Hide toggle, 2FA card
7. "Save profile" and "Update password" should work as before
8. "Save Virtual Hall" should show mock notification

**If all checks pass, commit:**

```bash
git add -A && git commit -m "feat: redesign Settings page with Profile, Virtual Hall customization, and Security sections"
```

**Do NOT push yet. Wait for Max to review.**

---

## Files changed summary

| File | Action |
|------|--------|
| `resources/web/js/pages/Profile.vue` | **REWRITTEN** — full redesign |
| `resources/web/js/router/routes.js` | **MODIFIED** — rename route to 'settings' |

## Files NOT touched

| File | Reason |
|------|--------|
| `resources/web/js/services/profile-data.js` | Preserved — still used |
| `resources/web/js/parts/custom-select-countries.vue` | Preserved — still used |
| Backend (controllers, routes) | No backend changes |
| `resources/web/js/components/sidebar.vue` | No sidebar changes — Profile is accessed via header avatar, not sidebar |
