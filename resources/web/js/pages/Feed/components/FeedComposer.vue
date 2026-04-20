<template>
  <div class="composer">
    <div class="composer-label">New achievement</div>

    <!-- Type selector — always visible -->
    <div class="composer-types">
      <button
        class="ctype"
        :class="{ active: selectedType === 'trophy' }"
        @click="selectType('trophy')"
      >
        <span class="cdot"></span> Trophy
      </button>
      <button
        class="ctype"
        :class="{ active: selectedType === 'image' }"
        @click="selectType('image')"
      >
        <span class="cdot"></span> Image
      </button>
      <button class="ctype disabled" disabled>
        <span class="cdot"></span> Video
        <span class="soon">soon</span>
      </button>
      <button class="ctype disabled" disabled>
        <span class="cdot"></span> Clip from Twitch / Overwolf
        <span class="soon">soon</span>
      </button>
    </div>

    <!-- Expanded: Image mode -->
    <div v-if="selectedType === 'image'">
      <div class="composer-sep"></div>

      <div class="icomp-field">
        <div class="icomp-label">Category</div>
        <div class="icomp-cats">
          <button
            v-for="cat in categories"
            :key="cat"
            class="icomp-cat"
            :class="{ active: selectedCategory === cat }"
            @click="selectedCategory = cat"
          >{{ cat }}</button>
        </div>
      </div>

      <div class="icomp-field">
        <div class="icomp-label">Image</div>
        <div
          v-if="!previewImage"
          class="icomp-upload"
          @click="triggerFileInput"
          @dragover.prevent
          @drop.prevent="handleDrop"
        >
          <div class="icomp-upload-icon">&#8682;</div>
          <div class="icomp-upload-text">
            <strong>Upload a file</strong> or drag and drop
          </div>
          <div class="icomp-upload-sub">PNG, JPG up to 10MB</div>
        </div>
        <div v-else class="upload-preview">
          <img :src="previewImage" alt="Preview" class="preview-img" />
          <div class="preview-remove" @click="clearImage">&#10005;</div>
        </div>
        <input
          ref="fileInput"
          type="file"
          accept=".jpg,.jpeg,.png"
          style="display: none;"
          @change="handleFileChange"
        />
      </div>

      <div class="icomp-field">
        <label class="icomp-label">Name</label>
        <input
          v-model="name"
          type="text"
          class="icomp-input"
          placeholder="Name your achievement"
          maxlength="255"
        />
      </div>

      <div class="icomp-field">
        <label class="icomp-label">Description</label>
        <textarea
          v-model="description"
          class="icomp-textarea"
          placeholder="Tell the story behind it..."
          maxlength="255"
          rows="3"
        ></textarea>
        <div class="icomp-charcount">{{ description.length }}/255</div>
      </div>

      <div class="composer-actions">
        <button class="cbtn cancel" @click="resetComposer">Cancel</button>
        <button
          class="cbtn publish"
          :disabled="!canPublishImage"
          @click="publishImage"
        >
          {{ publishing ? 'Publishing...' : 'Publish' }}
        </button>
      </div>
    </div>

    <!-- Expanded: Trophy mode -->
    <div v-if="selectedType === 'trophy'">
      <div class="composer-sep"></div>

      <div class="trophy-selector-label">Select a forged trophy</div>
      <div v-if="loadingTrophies" class="loading-trophies">Loading trophies...</div>
      <div v-else-if="forgedTrophies.length === 0" class="no-trophies">
        No forged trophies yet. Visit the Forge to create one.
      </div>
      <div v-else class="trophy-options">
        <div
          v-for="trophy in forgedTrophies"
          :key="trophy.id"
          class="trophy-opt"
          :class="{ selected: selectedTrophy && selectedTrophy.id === trophy.id }"
          @click="selectedTrophy = trophy"
        >
          <div class="trophy-opt-icon">&#9670;</div>
          <div class="trophy-opt-name">{{ trophy.name }}</div>
        </div>
      </div>

      <div class="composer-comment">
        <label>Comment (optional)</label>
        <textarea
          v-model="trophyComment"
          class="composer-comment-input"
          placeholder="Say something about this trophy..."
          maxlength="255"
          rows="2"
        ></textarea>
        <div class="composer-comment-count">{{ trophyComment.length }}/255</div>
      </div>

      <div class="composer-actions">
        <button class="cbtn cancel" @click="resetComposer">Cancel</button>
        <button
          class="cbtn publish"
          :disabled="!selectedTrophy"
          @click="publishTrophy"
        >
          {{ publishing ? 'Publishing...' : 'Publish' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import api from "../../../api/api.js";
import store from "../../../store/store.js";

export default defineComponent({
  data() {
    return {
      selectedType: null,
      selectedCategory: null,
      categories: ["Gaming", "Life", "Social", "Sports", "Creative"],
      name: "",
      description: "",
      previewImage: null,
      selectedFile: null,
      publishing: false,
      // Trophy mode
      forgedTrophies: [],
      selectedTrophy: null,
      trophyComment: "",
      loadingTrophies: false,
    };
  },
  computed: {
    canPublishImage() {
      return (
        this.selectedFile &&
        this.name.trim().length > 0 &&
        this.description.trim().length > 0 &&
        !this.publishing
      );
    },
  },
  methods: {
    selectType(type) {
      if (this.selectedType === type) {
        this.selectedType = null;
        return;
      }
      this.selectedType = type;
      if (type === "trophy") {
        this.fetchForgedTrophies();
      }
    },

    triggerFileInput() {
      this.$refs.fileInput.click();
    },

    handleFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedFile = file;
        const reader = new FileReader();
        reader.onload = () => {
          this.previewImage = reader.result;
        };
        reader.readAsDataURL(file);
      }
    },

    handleDrop(event) {
      const file = event.dataTransfer.files[0];
      if (file) {
        this.selectedFile = file;
        const reader = new FileReader();
        reader.onload = () => {
          this.previewImage = reader.result;
        };
        reader.readAsDataURL(file);
      }
    },

    clearImage() {
      this.previewImage = null;
      this.selectedFile = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },

    resetComposer() {
      this.selectedType = null;
      this.selectedCategory = null;
      this.name = "";
      this.description = "";
      this.previewImage = null;
      this.selectedFile = null;
      this.publishing = false;
      this.selectedTrophy = null;
      this.trophyComment = "";
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = "";
      }
    },

    async publishImage() {
      if (!this.canPublishImage) return;
      this.publishing = true;

      try {
        const formData = new FormData();
        formData.append("image", this.selectedFile);
        formData.append("name", this.name.trim());
        formData.append("description", this.description.trim());

        const response = await api.post("/api/feed/create-achievement", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        if (response && response.data) {
          this.resetComposer();
          this.$emit("post-created");
        }
      } catch (error) {
        console.error("Error creating achievement:", error);
        alert(
          error.response?.data?.message || "Failed to create achievement"
        );
      } finally {
        this.publishing = false;
      }
    },

    async fetchForgedTrophies() {
      this.loadingTrophies = true;
      try {
        const response = await api.get("/api/forge/trophies");
        if (response && response.data) {
          this.forgedTrophies = response.data.trophies || response.data.data || response.data || [];
        }
      } catch (error) {
        console.error("Error fetching trophies:", error);
        this.forgedTrophies = [];
      } finally {
        this.loadingTrophies = false;
      }
    },

    async publishTrophy() {
      if (!this.selectedTrophy || this.publishing) return;
      this.publishing = true;

      try {
        const response = await api.post("/api/feed/share", {
          id: this.selectedTrophy.id,
          type: "trophy",
        });

        if (response && response.data) {
          this.resetComposer();
          this.$emit("post-created");
        }
      } catch (error) {
        console.error("Error sharing trophy:", error);
        alert(
          error.response?.data?.message || "Failed to share trophy"
        );
      } finally {
        this.publishing = false;
      }
    },
  },
});
</script>

<style lang="scss" scoped>
.composer {
  position: sticky; top: 120px; z-index: 20;
  padding: 24px 28px;
  background: rgba(14,15,17,0.9);
  border: 1px solid rgba(255,97,0,0.15);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  margin-bottom: 32px;
}
.composer-label {
  font-size: 11px; color: var(--primary);
  letter-spacing: 0.25em; text-transform: uppercase;
  margin-bottom: 16px;
}
.composer-types { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.ctype {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 8px 14px; font-size: 11px;
  letter-spacing: 0.12em; text-transform: uppercase;
  border: 1px solid var(--border);
  color: var(--text-muted);
  transition: all 0.15s; cursor: pointer;
  font-family: var(--mono); background: none;
}
.ctype:hover { color: var(--text); border-color: var(--text-dim); }
.ctype.active {
  color: var(--bg); background: var(--accent);
  border-color: var(--accent);
  box-shadow: 0 0 12px var(--accent-glow);
}
.ctype.disabled { opacity: 0.4; cursor: default; pointer-events: none; }
.cdot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--text-dim);
}
.ctype.active .cdot { background: var(--bg); }
.soon {
  font-size: 8px; color: var(--text-dim);
  letter-spacing: 0.15em; margin-left: 2px;
}
.composer-sep {
  height: 1px;
  background: linear-gradient(90deg, rgba(255,97,0,0.15), transparent);
  margin: 16px 0;
}

/* Trophy selector */
.trophy-selector-label {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 12px;
}
.trophy-options { display: flex; flex-direction: column; gap: 2px; margin-bottom: 16px; }
.trophy-opt {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 16px;
  background: rgba(26,28,31,0.8);
  border: 1px solid var(--border);
  cursor: pointer; transition: all 0.15s;
}
.trophy-opt:hover { border-color: var(--primary); background: rgba(255,97,0,0.04); }
.trophy-opt.selected { border-color: var(--primary); background: rgba(255,97,0,0.06); }
.trophy-opt-icon {
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  color: var(--primary);
  font-size: 18px;
}
.trophy-opt-icon img { width: 28px; height: 28px; object-fit: contain; }
.trophy-opt-name { font-size: 13px; color: var(--text); letter-spacing: 0.03em; }

/* Image composer */
.icomp-label {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 10px;
}
.icomp-cats { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; }
.icomp-cat {
  padding: 6px 12px; font-size: 10px;
  letter-spacing: 0.15em; text-transform: uppercase;
  border: 1px solid var(--border);
  color: var(--text-muted); cursor: pointer;
  transition: all 0.15s;
  font-family: var(--mono); background: none;
}
.icomp-cat:hover { color: var(--text); border-color: var(--text-dim); }
.icomp-cat.active { color: var(--bg); background: var(--accent); border-color: var(--accent); }
.icomp-upload {
  border: 1px dashed rgba(255,97,0,0.25);
  padding: 32px; text-align: center;
  margin-bottom: 16px; cursor: pointer;
  transition: all 0.15s;
}
.icomp-upload:hover { border-color: var(--primary); background: rgba(255,97,0,0.03); }
.icomp-upload-icon {
  color: var(--text-dim); margin-bottom: 8px;
  font-size: 24px;
}
.icomp-upload-text { font-size: 12px; color: var(--text-muted); letter-spacing: 0.04em; }
.icomp-upload-text strong { color: var(--text); }
.icomp-upload-sub { font-size: 10px; color: var(--text-dim); margin-top: 4px; letter-spacing: 0.08em; }
.icomp-field { margin-bottom: 12px; }
.icomp-field label {
  display: block; font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 6px;
}
.icomp-input {
  width: 100%; padding: 10px 14px;
  background: var(--surface-2); border: 1px solid var(--border);
  color: var(--text); font-size: 13px; letter-spacing: 0.03em;
  font-family: var(--mono);
  transition: border-color 0.15s;
}
.icomp-input:focus { border-color: var(--primary); outline: none; }
.icomp-input::placeholder { color: var(--text-dim); }
.icomp-textarea {
  width: 100%; padding: 10px 14px;
  background: var(--surface-2); border: 1px solid var(--border);
  color: var(--text); font-size: 13px; letter-spacing: 0.03em;
  font-family: var(--mono);
  min-height: 80px; resize: vertical;
  transition: border-color 0.15s;
}
.icomp-textarea:focus { border-color: var(--primary); outline: none; }
.icomp-textarea::placeholder { color: var(--text-dim); }
.icomp-charcount {
  font-size: 9px; color: var(--text-dim);
  letter-spacing: 0.15em; text-align: right; margin-top: 4px;
}

/* Preview */
.upload-preview { position: relative; margin-bottom: 16px; }
.preview-img {
  width: 100%; max-height: 240px; object-fit: cover;
  border: 1px solid rgba(42,44,46,0.6);
}
.preview-remove {
  position: absolute; top: 8px; right: 8px;
  width: 28px; height: 28px;
  background: rgba(0,0,3,0.8); border: 1px solid var(--border);
  color: var(--text-muted); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.15s;
}
.preview-remove:hover { color: var(--text); border-color: var(--text-dim); }

/* Composer comment (trophy mode) */
.composer-comment { margin-top: 4px; }
.composer-comment label {
  display: block; font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.2em; text-transform: uppercase;
  margin-bottom: 6px;
}
.composer-comment-input {
  width: 100%; padding: 10px 14px;
  background: var(--surface-2); border: 1px solid var(--border);
  color: var(--text); font-size: 13px; font-family: var(--mono);
  min-height: 70px; resize: vertical;
  transition: border-color 0.15s;
}
.composer-comment-input:focus { border-color: var(--primary); outline: none; }
.composer-comment-input::placeholder { color: var(--text-dim); }
.composer-comment-count {
  font-size: 9px; color: var(--text-dim);
  letter-spacing: 0.15em; text-align: right; margin-top: 4px;
}

/* Composer actions */
.composer-actions {
  display: flex; align-items: center; justify-content: flex-end; gap: 10px;
  margin-top: 16px; padding-top: 16px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.cbtn {
  padding: 10px 20px; font-size: 10px;
  letter-spacing: 0.2em; text-transform: uppercase;
  transition: all 0.15s; cursor: pointer;
  font-family: var(--mono);
}
.cbtn.cancel {
  color: var(--text-muted); border: 1px solid var(--border); background: transparent;
}
.cbtn.cancel:hover { color: var(--text); border-color: var(--text-dim); }
.cbtn.publish {
  color: var(--bg); background: var(--primary); border: 1px solid var(--primary);
  box-shadow: 0 0 14px rgba(255,97,0,0.3);
}
.cbtn.publish:hover { background: #ff7e2e; box-shadow: 0 0 24px rgba(255,97,0,0.5); }
.cbtn:disabled { opacity: 0.4; cursor: default; pointer-events: none; }

/* Loading / empty states */
.loading-trophies, .no-trophies {
  padding: 20px; text-align: center;
  font-size: 12px; color: var(--text-dim);
  letter-spacing: 0.08em;
}

@media (max-width: 700px) {
  .composer { padding: 18px 20px; top: 110px; }
  .composer-types { gap: 6px; }
  .ctype { padding: 6px 10px; font-size: 10px; }
}
</style>
