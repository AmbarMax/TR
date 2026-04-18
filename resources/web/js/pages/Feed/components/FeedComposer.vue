<template>
  <div class="composer">
    <div class="composer-label">New achievement</div>

    <!-- Type selector — always visible -->
    <div class="composer-types">
      <div
        class="type-btn"
        :class="{ sel: selectedType === 'trophy' }"
        @click="selectType('trophy')"
      >
        <span class="dot dot-trophy"></span> Trophy
      </div>
      <div
        class="type-btn"
        :class="{ sel: selectedType === 'image' }"
        @click="selectType('image')"
      >
        <span class="dot dot-image"></span> Image
      </div>
      <div class="type-btn soon">
        <span class="dot dot-disabled"></span> Video
        <span class="soon-tag">soon</span>
      </div>
      <div class="type-btn soon">
        <span class="dot dot-disabled"></span> Clip from Twitch / Overwolf
        <span class="soon-tag">soon</span>
      </div>
    </div>

    <!-- Expanded: Image mode -->
    <div v-if="selectedType === 'image'" class="composer-form">
      <div class="form-section">
        <div class="form-label">Category</div>
        <div class="category-pills">
          <div
            v-for="cat in categories"
            :key="cat"
            class="cat-pill"
            :class="{ sel: selectedCategory === cat }"
            @click="selectedCategory = cat"
          >
            {{ cat }}
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="form-label">Image</div>
        <div
          v-if="!previewImage"
          class="upload-zone"
          @click="triggerFileInput"
          @dragover.prevent
          @drop.prevent="handleDrop"
        >
          <div class="upload-icon">&#8682;</div>
          <div class="upload-text">
            <span class="upload-link">Upload a file</span> or drag and drop<br>PNG, JPG up to 10MB
          </div>
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

      <div class="form-section">
        <div class="form-label">Name</div>
        <input
          v-model="name"
          type="text"
          class="form-input"
          placeholder="Name your achievement"
          maxlength="255"
        />
      </div>

      <div class="form-section">
        <div class="form-label">Description</div>
        <textarea
          v-model="description"
          class="form-textarea"
          placeholder="Tell the story behind it..."
          maxlength="255"
          rows="3"
        ></textarea>
        <div class="char-count">{{ description.length }}/255</div>
      </div>

      <div class="composer-actions">
        <button class="btn-cancel" @click="resetComposer">Cancel</button>
        <button
          class="btn-publish"
          :disabled="!canPublishImage"
          @click="publishImage"
        >
          {{ publishing ? 'Publishing...' : 'Publish' }}
        </button>
      </div>
    </div>

    <!-- Expanded: Trophy mode -->
    <div v-if="selectedType === 'trophy'" class="composer-form">
      <div class="form-section">
        <div class="form-label">Select a forged trophy</div>
        <div v-if="loadingTrophies" class="loading-text">Loading trophies...</div>
        <div v-else-if="forgedTrophies.length === 0" class="empty-text">
          No forged trophies yet. Visit the Forge to create one.
        </div>
        <div v-else class="trophy-list">
          <div
            v-for="trophy in forgedTrophies"
            :key="trophy.id"
            class="trophy-option"
            :class="{ sel: selectedTrophy && selectedTrophy.id === trophy.id }"
            @click="selectedTrophy = trophy"
          >
            <div class="trophy-icon">&#9670;</div>
            <div class="trophy-info">
              <div class="trophy-name">{{ trophy.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="form-label">Comment (optional)</div>
        <textarea
          v-model="trophyComment"
          class="form-textarea"
          placeholder="Say something about this trophy..."
          maxlength="255"
          rows="2"
        ></textarea>
        <div class="char-count">{{ trophyComment.length }}/255</div>
      </div>

      <div class="composer-actions">
        <button class="btn-cancel" @click="resetComposer">Cancel</button>
        <button
          class="btn-publish"
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

<style scoped>
.composer {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 16px;
}
.composer-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  color: #ff6100;
  letter-spacing: 0.12em;
  margin-bottom: 12px;
}
.composer-types {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.type-btn {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 6px 12px;
  border-radius: 4px;
  border: 1px solid #2a2c2e;
  background: #1a1c1f;
  color: #9a9590;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: all 0.15s;
}
.type-btn:hover:not(.soon) {
  border-color: #5a5550;
  color: #feeddf;
}
.type-btn.sel {
  border-color: #ff6100;
  color: #ff6100;
  background: rgba(255, 97, 0, 0.08);
}
.type-btn.soon {
  opacity: 0.35;
  cursor: default;
}
.dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot-trophy {
  background: #ff6100;
}
.dot-image {
  background: #c1f527;
}
.dot-disabled {
  background: #5a5550;
}
.soon-tag {
  font-size: 8px;
  color: #ff6100;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

/* Form area */
.composer-form {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #2a2c2e;
}
.form-section {
  margin-bottom: 14px;
}
.form-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #5a5550;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-bottom: 8px;
}

/* Category pills */
.category-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.cat-pill {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  padding: 5px 12px;
  border-radius: 14px;
  border: 1px solid #2a2c2e;
  background: #1a1c1f;
  color: #9a9590;
  cursor: pointer;
  transition: all 0.15s;
}
.cat-pill:hover {
  border-color: #5a5550;
  color: #feeddf;
}
.cat-pill.sel {
  border-color: #c1f527;
  color: #c1f527;
  background: rgba(193, 245, 39, 0.08);
}

/* Upload zone */
.upload-zone {
  border: 1px dashed #2a2c2e;
  border-radius: 6px;
  padding: 24px;
  text-align: center;
  background: #0a0b0d;
  cursor: pointer;
  transition: border-color 0.15s;
}
.upload-zone:hover {
  border-color: #5a5550;
}
.upload-icon {
  font-size: 20px;
  color: #5a5550;
  margin-bottom: 6px;
}
.upload-text {
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  color: #5a5550;
  line-height: 1.6;
}
.upload-link {
  color: #feeddf;
  cursor: pointer;
}

/* Upload preview */
.upload-preview {
  position: relative;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  overflow: hidden;
  max-height: 200px;
}
.preview-img {
  width: 100%;
  max-height: 200px;
  object-fit: cover;
  display: block;
}
.preview-remove {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 3, 0.7);
  border: 1px solid #2a2c2e;
  color: #9a9590;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.preview-remove:hover {
  color: #ff6100;
  border-color: #ff6100;
}

/* Inputs */
.form-input {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 4px;
  padding: 8px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #feeddf;
  outline: none;
  transition: border-color 0.15s;
  box-sizing: border-box;
}
.form-input:focus {
  border-color: #5a5550;
}
.form-textarea {
  width: 100%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 4px;
  padding: 8px 12px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #feeddf;
  outline: none;
  resize: vertical;
  min-height: 52px;
  transition: border-color 0.15s;
  box-sizing: border-box;
}
.form-textarea:focus {
  border-color: #5a5550;
}
.char-count {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
  color: #5a5550;
  text-align: right;
  margin-top: 4px;
}

/* Trophy list */
.trophy-list {
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 6px;
  max-height: 200px;
  overflow-y: auto;
}
.trophy-list::-webkit-scrollbar {
  width: 4px;
}
.trophy-list::-webkit-scrollbar-thumb {
  background: #ff6100;
  border-radius: 4px;
}
.trophy-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 2px;
  transition: background 0.15s;
}
.trophy-option:hover {
  background: rgba(255, 97, 0, 0.06);
}
.trophy-option.sel {
  background: rgba(255, 97, 0, 0.1);
  border: 1px solid rgba(255, 97, 0, 0.3);
}
.trophy-icon {
  width: 32px;
  height: 32px;
  background: rgba(255, 97, 0, 0.12);
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ff6100;
  font-size: 14px;
  flex-shrink: 0;
}
.trophy-info {
  flex: 1;
}
.trophy-name {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #feeddf;
}
.loading-text,
.empty-text {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  color: #5a5550;
  padding: 12px 0;
}

/* Action buttons */
.composer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 4px;
}
.btn-cancel {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  padding: 8px 16px;
  border-radius: 4px;
  border: 1px solid #2a2c2e;
  background: transparent;
  color: #9a9590;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-cancel:hover {
  border-color: #5a5550;
  color: #feeddf;
}
.btn-publish {
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  padding: 8px 20px;
  border-radius: 4px;
  border: none;
  background: #ff6100;
  color: #feeddf;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-publish:hover {
  background: #e55800;
}
.btn-publish:disabled {
  opacity: 0.4;
  cursor: default;
}

@media (max-width: 968px) {
  .composer-types {
    gap: 6px;
  }
  .type-btn {
    font-size: 10px;
    padding: 5px 8px;
  }
}
</style>
