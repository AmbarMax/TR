<template>
  <div class="settings-page">

    <!-- PAGE HERO -->
    <div class="page-hero">
      <div class="page-hero-bg"></div>
      <div class="page-hero-inner">
        <div class="page-tag">Profile</div>
        <h1 class="page-title"><span class="accent-word">Settings</span></h1>
        <p class="page-subtitle">Manage your profile, Virtual Hall, and security</p>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="settings-content">

      <!-- ═══ BANNER + AVATAR ═══ -->
      <div class="settings-banner" @click="openInputBackground">
        <img v-if="background !== null" :src="getBackground()" alt="background" class="settings-banner-img" />
        <div v-else class="settings-banner-placeholder">
          <span>Click to change background</span>
        </div>
        <input accept=".jpg, .jpeg, .png" name="background" ref="fileInputBackground" type="file" @change="uploadBackground" style="display: none" />
      </div>
      <div class="settings-avatar-row">
        <div class="settings-avatar-ring" @click="openInputAvatar">
          <img v-if="avatar !== null" :src="getAvatar()" alt="avatar" class="settings-avatar-img" />
          <span v-else class="settings-avatar-placeholder">{{ getInitials(name) }}</span>
        </div>
        <button class="settings-avatar-btn" @click="openInputAvatar">Change avatar</button>
        <input accept=".jpg, .jpeg, .png" name="avatar" ref="fileInput" type="file" @change="uploadAvatar" style="display: none" />
      </div>

      <!-- ═══ SECTION: PROFILE ═══ -->
      <div class="section-label">
        <span class="label-text">Profile</span>
        <span class="section-meta">Personal info</span>
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" autocomplete="off" class="form-input" v-model="name" placeholder="Your name" />
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" autocomplete="off" class="form-input" v-model="username" @input="filterUsername" placeholder="username" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" autocomplete="off" class="form-input" v-model="email" placeholder="your@email.com" />
      </div>

      <div class="form-group">
        <label class="form-label">Tagline</label>
        <input type="text" autocomplete="off" class="form-input" v-model="description" placeholder="A short bio for your Virtual Hall" />
      </div>

      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="tel" autocomplete="off" class="form-input" v-model="phone_number" placeholder="Optional" />
        </div>
        <div class="form-group">
          <label class="form-label">Date of birth</label>
          <VueDatePicker
            class="settings-datepicker"
            v-model="date_of_birth"
            dark
            auto-apply
            :enable-time-picker="false"
            :max-date="maxDate"
            :only-calendar="true"
          />
        </div>
        <div class="form-group">
          <label class="form-label">Country</label>
          <CustomSelectCountries
            v-if="allCountries.length"
            @handleCountry="handleChildData"
            :id="country_id"
            :options="allCountries"
            class="settings-country-select"
          />
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-save" @click="updateProfile">Save profile</button>
      </div>

      <!-- ═══ SECTION: VIRTUAL HALL ═══ -->
      <div class="section-divider"></div>
      <div class="section-label">
        <span class="label-text">Virtual Hall</span>
        <span class="section-meta">Public profile customization</span>
      </div>

      <!-- Featured Slots -->
      <div class="subsection-label">Featured (3 slots)</div>
      <div class="featured-grid">
        <div
          v-for="(slot, index) in featuredSlots"
          :key="index"
          class="featured-slot"
          :class="{ 'featured-empty': !slot.item }"
          @click="openFeaturedPicker(index)"
        >
          <template v-if="slot.item">
            <div class="featured-icon">
              <img v-if="slot.item.image" :src="slot.item.image" alt="featured" class="featured-icon-img" />
              <span v-else class="featured-icon-letter">{{ slot.item.type === 'trophy' ? 'T' : slot.item.type === 'badge' ? 'B' : 'A' }}</span>
            </div>
            <div class="featured-info">
              <div class="featured-name">{{ slot.item.name }}</div>
              <div class="featured-type">{{ slot.item.type }}</div>
            </div>
            <span class="featured-change">Change</span>
          </template>
          <template v-else>
            <div class="featured-add">+</div>
            <div class="featured-add-text">Add featured</div>
          </template>
        </div>
      </div>

      <!-- Social Links -->
      <div class="subsection-label">Social links</div>
      <div class="form-grid-2">
        <div class="form-group form-group-icon">
          <label class="form-label">Twitter / X</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.twitter" placeholder="@handle" />
          </div>
        </div>
        <div class="form-group form-group-icon">
          <label class="form-label">Twitch</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.twitch" placeholder="channel" />
          </div>
        </div>
        <div class="form-group form-group-icon">
          <label class="form-label">YouTube</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12z"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.youtube" placeholder="channel URL" />
          </div>
        </div>
        <div class="form-group form-group-icon">
          <label class="form-label">Instagram</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.instagram" placeholder="@handle" />
          </div>
        </div>
        <div class="form-group form-group-icon">
          <label class="form-label">Discord</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.095 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.095 2.157 2.42 0 1.333-.947 2.418-2.157 2.418z"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.discord" placeholder="username#0000" />
          </div>
        </div>
        <div class="form-group form-group-icon">
          <label class="form-label">Website</label>
          <div class="input-icon-wrap">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <input type="text" autocomplete="off" class="form-input with-icon" v-model="socialLinks.website" placeholder="https://..." />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-save" @click="saveVirtualHall">Save Virtual Hall</button>
      </div>

      <!-- ═══ SECTION: SECURITY ═══ -->
      <div class="section-divider"></div>
      <div class="section-label">
        <span class="label-text">Security</span>
        <span class="section-meta">Password &amp; 2FA</span>
      </div>

      <!-- Change Password -->
      <div class="subsection-label">Change password</div>
      <div class="form-grid-3">
        <div class="form-group">
          <label class="form-label">Old password</label>
          <div class="input-password-wrap">
            <input
              :type="passEyes.old ? 'text' : 'password'"
              autocomplete="off"
              class="form-input with-eye"
              v-model="oldPassword"
              placeholder="Current password"
            />
            <span class="input-eye" @click="passEyes.old = !passEyes.old">{{ passEyes.old ? 'Hide' : 'Show' }}</span>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">New password</label>
          <div class="input-password-wrap">
            <input
              :type="passEyes.new ? 'text' : 'password'"
              autocomplete="off"
              class="form-input with-eye"
              v-model="newPassword"
              placeholder="New password"
            />
            <span class="input-eye" @click="passEyes.new = !passEyes.new">{{ passEyes.new ? 'Hide' : 'Show' }}</span>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm password</label>
          <div class="input-password-wrap">
            <input
              :type="passEyes.confirm ? 'text' : 'password'"
              autocomplete="off"
              class="form-input with-eye"
              v-model="confirmPassword"
              placeholder="Confirm new password"
            />
            <span class="input-eye" @click="passEyes.confirm = !passEyes.confirm">{{ passEyes.confirm ? 'Hide' : 'Show' }}</span>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn-save" @click="updatePassword">Update password</button>
      </div>

      <!-- 2FA -->
      <div class="subsection-label">Two-factor authentication</div>
      <div class="tfa-card" :class="$store.state.google2fa_status ? 'tfa-active' : 'tfa-inactive'">
        <div class="tfa-header">
          <div class="tfa-title">2FA Status</div>
          <div class="tfa-badge" :class="$store.state.google2fa_status ? 'tfa-badge-on' : 'tfa-badge-off'">
            <span v-if="$store.state.google2fa_status" class="tfa-dot"></span>
            {{ $store.state.google2fa_status ? 'Active' : 'Inactive' }}
          </div>
        </div>
        <div class="tfa-actions">
          <template v-if="!$store.state.google2fa_status">
            <button class="btn-tfa-activate" @click="activate2fa">Activate 2FA</button>
          </template>
          <template v-else>
            <button class="btn-tfa-deactivate" @click="deactivate2fa">Deactivate</button>
            <button class="btn-tfa-change" @click="activate2fa">Change code</button>
          </template>
        </div>
      </div>

    </div>

    <!-- TERMINAL STRIP -->
    <div class="terminal-strip">
      <div>settings · profile loaded<span class="cursor-blink"></span></div>
      <div>status · nominal</div>
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
          this.socialLinks.twitter   = user.social_twitter   || "";
          this.socialLinks.twitch    = user.social_twitch    || "";
          this.socialLinks.youtube   = user.social_youtube   || "";
          this.socialLinks.instagram = user.social_instagram || "";
          this.socialLinks.discord   = user.social_discord_tag || "";
          this.socialLinks.website   = user.social_website   || "";
          if (Array.isArray(user.featured_slots)) {
            this.featuredSlots = user.featured_slots.map((s) => ({ item: s || null }));
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
      console.log("Open featured picker for slot", index);
      store.state.notification = {
        message: "Featured picker coming soon (backend needed)",
        type: "success",
        show: true,
      };
    },
    saveVirtualHall() {
      api
        .put("/api/profile/update-virtual-hall", {
          social_twitter:     this.socialLinks.twitter,
          social_twitch:      this.socialLinks.twitch,
          social_youtube:     this.socialLinks.youtube,
          social_instagram:   this.socialLinks.instagram,
          social_discord_tag: this.socialLinks.discord,
          social_website:     this.socialLinks.website,
          featured_slots:     this.featuredSlots.map((s) => s.item),
        })
        .then((response) => {
          store.state.notification = {
            message: "Virtual Hall updated",
            type: "success",
            show: true,
          };
        })
        .catch(() => {
          store.state.notification = {
            message: "Failed to save Virtual Hall",
            type: "error",
            show: true,
          };
        });
    },
  },
  mounted() {
    this.getAllCountries();
  },
};
</script>

<style lang="scss" scoped>
.settings-page {
  min-width: 0;
  max-width: 100%;
}

/* Page Hero */
.page-hero {
  position: relative;
  padding: 40px 48px 0;
  overflow: hidden;
}
.page-hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 700px 400px at 90% 40%, rgba(255,97,0,0.1), transparent 60%),
    radial-gradient(ellipse 400px 300px at 90% 40%, rgba(193,245,39,0.03), transparent 65%);
}
.page-hero-inner { position: relative; z-index: 2; }
.page-tag {
  display: inline-flex; align-items: center; gap: 10px;
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.32em; text-transform: uppercase;
  margin-bottom: 16px;
}
.page-tag::before {
  content: ''; width: 28px; height: 1px; background: var(--primary);
  box-shadow: 0 0 6px var(--primary);
}
.page-title {
  font-family: var(--display);
  font-size: 64px; line-height: 0.95;
  color: var(--text); letter-spacing: 0.015em;
  margin-bottom: 10px;
  text-shadow: 0 0 30px rgba(255,97,0,0.18);
}
.page-title .accent-word {
  color: var(--primary);
  text-shadow: 0 0 22px var(--primary-glow);
}
.page-subtitle {
  font-size: 13px; color: var(--text-muted);
  letter-spacing: 0.04em; line-height: 1.6;
  max-width: 580px;
}

/* Content wrapper */
.settings-content {
  padding: 32px 48px 48px;
  max-width: 900px;
}

/* ═══ BANNER + AVATAR ═══ */
.settings-banner {
  width: 100%; height: 180px;
  border: 1px solid rgba(255,97,0,0.15);
  overflow: hidden; cursor: pointer;
  position: relative;
  transition: border-color 0.2s;
  margin-bottom: 0;
}
.settings-banner:hover { border-color: var(--primary); }
.settings-banner-img { width: 100%; height: 100%; object-fit: cover; }
.settings-banner-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, rgba(255,97,0,0.08), rgba(193,245,39,0.04));
  font-size: 12px; color: var(--text-dim); letter-spacing: 0.1em;
}

.settings-avatar-row {
  display: flex; align-items: center; gap: 16px;
  margin-top: -36px; margin-left: 24px; margin-bottom: 32px;
  position: relative; z-index: 2;
}
.settings-avatar-ring {
  width: 72px; height: 72px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 0 0 3px var(--primary), 0 0 18px var(--primary-glow);
  overflow: hidden;
  background: var(--surface);
}
.settings-avatar-img { width: 100%; height: 100%; object-fit: cover; }
.settings-avatar-placeholder {
  font-size: 22px; color: var(--text); font-weight: bold;
  font-family: var(--display);
}
.settings-avatar-btn {
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--primary); background: none;
  border: 1px solid rgba(255,97,0,0.3);
  padding: 6px 14px; cursor: pointer;
  transition: all 0.15s;
}
.settings-avatar-btn:hover {
  border-color: var(--primary);
  background: rgba(255,97,0,0.06);
}

/* ═══ SECTION LABELS ═══ */
.section-label {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 24px; margin-top: 8px;
}
.label-text {
  font-size: 11px; color: var(--primary);
  letter-spacing: 0.25em; text-transform: uppercase;
  white-space: nowrap;
}
.label-text::after {
  content: ''; display: inline-block;
  width: 60px; height: 1px; margin-left: 12px;
  background: linear-gradient(90deg, rgba(255,97,0,0.3), transparent);
  vertical-align: middle;
}
.section-meta {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.1em;
}
.subsection-label {
  font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 14px; margin-top: 24px;
}
.section-divider {
  height: 1px; margin: 40px 0;
  background: linear-gradient(90deg, rgba(255,97,0,0.15), transparent 80%);
}

/* ═══ FORM ELEMENTS ═══ */
.form-grid-2 {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 16px; margin-bottom: 16px;
}
.form-grid-3 {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  gap: 16px; margin-bottom: 16px;
}
.form-group { margin-bottom: 16px; }
.form-label {
  display: block; font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 6px;
}
.form-input {
  width: 100%; padding: 10px 14px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono);
  font-size: 13px; letter-spacing: 0.03em;
  transition: border-color 0.15s, background 0.15s;
  box-sizing: border-box;
}
.form-input:focus {
  outline: none;
  border-color: var(--primary);
  background: rgba(255,97,0,0.03);
  box-shadow: 0 0 0 1px rgba(255,97,0,0.15);
}
.form-input::placeholder { color: var(--text-dim); }

/* Social link inputs with icon */
.input-icon-wrap {
  position: relative;
}
.input-icon {
  position: absolute; left: 14px; top: 50%;
  transform: translateY(-50%);
  color: var(--text-dim);
  pointer-events: none;
  z-index: 1;
}
.form-input.with-icon { padding-left: 40px; }

/* Password inputs with show/hide */
.input-password-wrap { position: relative; }
.form-input.with-eye { padding-right: 60px; }
.input-eye {
  position: absolute; right: 14px; top: 50%;
  transform: translateY(-50%);
  font-size: 10px; letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--text-muted); cursor: pointer;
  transition: color 0.15s;
}
.input-eye:hover { color: var(--primary); }

/* Save buttons */
.form-actions {
  margin-top: 8px; margin-bottom: 8px;
}
.btn-save {
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  padding: 12px 28px;
  background: var(--accent); color: var(--bg);
  border: 1px solid var(--accent);
  cursor: pointer; transition: all 0.15s;
  box-shadow: 0 0 20px rgba(193,245,39,0.25);
}
.btn-save:hover {
  background: #d4ff4a;
  box-shadow: 0 0 32px rgba(193,245,39,0.45);
}

/* ═══ FEATURED SLOTS ═══ */
.featured-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 12px; margin-bottom: 24px;
}
.featured-slot {
  padding: 16px;
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(42,44,46,0.8);
  cursor: pointer;
  transition: all 0.2s;
  display: flex; align-items: center; gap: 12px;
}
.featured-slot:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3), 0 0 12px rgba(255,97,0,0.1);
}
.featured-empty {
  border-style: dashed;
  border-color: rgba(255,97,0,0.2);
  justify-content: center;
  flex-direction: column;
  text-align: center;
}
.featured-empty:hover { border-style: solid; border-color: var(--primary); }
.featured-icon {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  background: var(--surface-2); border: 1px solid var(--border);
  flex-shrink: 0;
}
.featured-icon-img { width: 32px; height: 32px; object-fit: contain; }
.featured-icon-letter {
  font-family: var(--display); font-size: 20px;
  color: var(--primary);
}
.featured-info { flex: 1; min-width: 0; }
.featured-name {
  font-family: var(--display); font-size: 20px;
  color: var(--text); letter-spacing: 0.02em;
}
.featured-type {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.15em; text-transform: uppercase;
}
.featured-change {
  font-size: 10px; color: var(--primary);
  letter-spacing: 0.1em; text-transform: uppercase;
}
.featured-add {
  font-family: var(--display); font-size: 32px;
  color: var(--text-dim);
}
.featured-add-text {
  font-size: 11px; color: var(--text-dim);
  letter-spacing: 0.1em;
}

/* ═══ 2FA CARD ═══ */
.tfa-card {
  padding: 24px;
  border: 1px solid var(--border);
  margin-bottom: 24px;
}
.tfa-active {
  background: rgba(193,245,39,0.04);
  border-color: rgba(193,245,39,0.3);
}
.tfa-inactive {
  background: rgba(14,15,17,0.7);
}
.tfa-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.tfa-title {
  font-size: 13px; color: var(--text);
  letter-spacing: 0.04em;
}
.tfa-badge {
  font-size: 9px; padding: 3px 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  display: flex; align-items: center; gap: 6px;
}
.tfa-badge-on {
  background: rgba(193,245,39,0.15); color: var(--accent);
}
.tfa-badge-off {
  background: var(--surface-3); color: var(--text-dim);
}
.tfa-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 0 8px var(--accent);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--accent); }
  50% { opacity: 0.5; box-shadow: 0 0 16px var(--accent); }
}
.tfa-actions { display: flex; gap: 10px; }
.btn-tfa-activate {
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  padding: 8px 18px;
  background: var(--accent); color: var(--bg);
  border: 1px solid var(--accent);
  cursor: pointer; transition: all 0.15s;
  box-shadow: 0 0 12px var(--accent-glow);
}
.btn-tfa-activate:hover { background: #d4ff4a; }
.btn-tfa-deactivate {
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  padding: 8px 18px;
  background: transparent; color: var(--text-muted);
  border: 1px solid var(--border);
  cursor: pointer; transition: all 0.15s;
}
.btn-tfa-deactivate:hover { color: #e24b4a; border-color: rgba(226,75,74,0.4); }
.btn-tfa-change {
  font-family: var(--mono); font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  padding: 8px 18px;
  background: transparent; color: var(--primary);
  border: 1px solid rgba(255,97,0,0.3);
  cursor: pointer; transition: all 0.15s;
}
.btn-tfa-change:hover { border-color: var(--primary); background: rgba(255,97,0,0.06); }

/* Terminal Strip */
.terminal-strip {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  display: flex; justify-content: space-between;
  padding: 20px 48px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.cursor-blink {
  display: inline-block; width: 8px; height: 11px;
  background: var(--primary);
  margin-left: 4px; vertical-align: middle;
  animation: blink 1s steps(1) infinite;
}
@keyframes blink { 50% { opacity: 0; } }

/* ═══ DATEPICKER OVERRIDES ═══ */
.settings-datepicker :deep(.dp__input) {
  background: rgba(14,15,17,0.6) !important;
  border: 1px solid var(--border) !important;
  color: var(--text) !important;
  font-family: var(--mono) !important;
  font-size: 13px !important;
  border-radius: 0 !important;
  padding: 10px 14px !important;
}
.settings-datepicker :deep(.dp__input:focus) {
  border-color: var(--primary) !important;
  box-shadow: 0 0 0 1px rgba(255,97,0,0.15) !important;
}

/* ═══ COUNTRY SELECT OVERRIDES ═══ */
.settings-country-select :deep(input) {
  width: 100%; padding: 10px 14px;
  background: rgba(14,15,17,0.6);
  border: 1px solid var(--border);
  color: var(--text); font-family: var(--mono);
  font-size: 13px; letter-spacing: 0.03em;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.15s;
}
.settings-country-select :deep(input:focus) {
  border-color: var(--primary);
  box-shadow: 0 0 0 1px rgba(255,97,0,0.15);
}
.settings-country-select :deep(.items) {
  background: var(--surface-2);
  border: 1px solid var(--border);
  margin-top: 4px;
  max-height: 240px;
  overflow-y: auto;
}
.settings-country-select :deep(.items div) {
  font-family: var(--mono);
  font-size: 13px;
  color: var(--text);
  padding: 8px 14px;
  cursor: pointer;
  transition: background 0.1s;
}
.settings-country-select :deep(.items div:hover) {
  background: rgba(255,97,0,0.08);
  color: var(--primary);
}

/* ═══ RESPONSIVE ═══ */
@media (max-width: 968px) {
  .form-grid-3 { grid-template-columns: 1fr 1fr; }
  .featured-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 700px) {
  .page-hero { padding: 28px 20px 0; }
  .page-title { font-size: 44px; }
  .settings-content { padding: 24px 20px 40px; }
  .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
  .featured-grid { grid-template-columns: 1fr; }
  .terminal-strip { padding: 20px; }
  .settings-banner { height: 120px; }
  .settings-avatar-row { margin-left: 16px; }
}
</style>
