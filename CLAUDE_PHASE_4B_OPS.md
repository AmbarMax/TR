# CLAUDE_PHASE_4B_OPS.md — New Composer + Validate Absorption

> **Operational brief for Claude Code.**
> Read `CLAUDE_FRONTEND_OPS.md` Section 2 for design system tokens.
> Phase 4B: New achievement composer in Feed, simplified backend endpoint, remove Validate page.
> **This phase touches backend (PHP).** Go carefully, one step at a time.

---

## Scope

1. Create a new backend endpoint that creates an achievement and publishes it to the feed in one step (no validation protocol)
2. Create a new FeedComposer.vue component in the Feed with type selection (Trophy / Image / Video soon / Clip soon) and category selection
3. Remove Validate from sidebar (keep the page file for now, just remove nav link)
4. Wire the composer to the backend

**Files to create:**
```
resources/web/js/pages/Feed/components/FeedComposer.vue    ← New composer component
```

**Files to modify:**
```
routes/api.php                                              ← Add new endpoint
app/Http/Controllers/Api/Feed/FeedController.php            ← Add createAchievement method
app/Services/Api/FeedService.php                            ← Add createAndShareAchievement method
resources/web/js/pages/Feed/Feed.vue                        ← Import and add FeedComposer
resources/web/js/components/sidebar.vue                     ← Remove Validate link
```

**DO NOT modify or delete:**
```
app/Http/Controllers/Api/Validate/AchievementController.php  ← Keep, don't break existing
app/Services/Api/AchievementService.php                       ← Keep, don't break existing
resources/web/js/pages/Validate.vue                           ← Keep file, just remove from nav
```

---

## Design Tokens Reference

```scss
$bg: #000003;
$surface: #0e0f11;
$surface-2: #1a1c1f;
$surface-3: #252729;
$primary: #ff6100;
$accent: #c1f527;
$text: #feeddf;
$text-muted: #9a9590;
$text-dim: #5a5550;
$border: #2a2c2e;
$mono: 'Share Tech Mono', monospace;
```

---

## Step-by-step Execution

### Step 1 — Backend: New endpoint for creating + publishing achievement

This step adds a simplified flow: create achievement → auto-publish to feed. No validation protocol. No Ambar cost (will be added in future phase).

#### 1A. Add method to FeedService.php

Open `app/Services/Api/FeedService.php`. Add this method AFTER the existing `shareTrophy` method. Do NOT modify any existing methods.

```php
/**
 * Create a custom achievement and publish it to the feed in one step.
 * No validation protocol required.
 */
public function createAndShareAchievement($request): array
{
    try {
        DB::beginTransaction();

        $user = $request->user();

        // Save image
        $fileService = app(\App\Services\FileService::class);
        $image = $fileService->saveAchievementImage($request->file('image'));

        // Create achievement — auto-validated, auto-shared
        $achievement = new \App\Models\Achievement();
        $achievement->user_id = $user->id;
        $achievement->status = \App\Models\Achievement::VALIDATED;
        $achievement->image = $image;
        $achievement->name = $request->input('name');
        $achievement->description = $request->input('description');
        $achievement->is_share = true;
        $achievement->save();

        // Create post (publish to feed)
        $achievement->posts()->create([
            'user_id' => $user->id,
        ]);

        DB::commit();

        return [
            'message' => 'Achievement created and published!',
            'status' => 200,
            'achievement_id' => $achievement->id,
        ];
    } catch (\Exception $e) {
        DB::rollBack();
        \Illuminate\Support\Facades\Log::error('FeedService@createAndShareAchievement: ' . $e->getMessage());
        return [
            'message' => $e->getMessage(),
            'status' => 500,
        ];
    }
}
```

#### 1B. Add method to FeedController.php

Open `app/Http/Controllers/Api/Feed/FeedController.php`. Add this method AFTER the existing `share` method. Do NOT modify any existing methods.

```php
public function createAchievement(Request $request): JsonResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
    ]);

    $result = $this->feedService->createAndShareAchievement($request);

    return response()->json([
        'message' => $result['message'],
        'achievement_id' => $result['achievement_id'] ?? null,
    ], $result['status']);
}
```

#### 1C. Add route to api.php

Open `routes/api.php`. Inside the `feed` prefix group (around line 226-240), add this route AFTER the existing `/share` route:

```php
Route::post('/create-achievement', [FeedController::class, 'createAchievement'])->name('createAchievement');
```

**Verify:** Run `php artisan route:list --path=feed` on the server to confirm the route exists. Or test locally if artisan is available:
```bash
grep "create-achievement" routes/api.php
```

---

### Step 2 — Frontend: FeedComposer.vue

Create `resources/web/js/pages/Feed/components/FeedComposer.vue`.

This component has 3 states:
- **Collapsed:** Shows placeholder text + type buttons
- **Expanded (Image):** Shows category pills + upload zone + name + description + publish
- **Expanded (Trophy):** Shows forged trophy list + optional comment + publish

```vue
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
          // Refresh the feed
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
        const response = await api.get("/api/forge/claimed");
        if (response && response.data) {
          this.forgedTrophies = response.data.data || response.data || [];
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
```

**Verify:** `npm run dev` — component should compile without errors.

---

### Step 3 — Wire FeedComposer into Feed.vue

Open `resources/web/js/pages/Feed/Feed.vue`.

#### 3A. Add import

In the `<script>` section, add:
```js
import FeedComposer from "./components/FeedComposer.vue";
```

Register in components:
```js
components: {
    MyFeed,
    Followers,
    WallOfFame,
    FeedComposer,
    store,
},
```

#### 3B. Add to template

Place `<FeedComposer />` inside `.feed-content`, ABOVE the tab content divs. Add the `@post-created` handler to refresh the feed.

The template should look like:
```html
<div class="feed-content">
  <FeedComposer @post-created="refreshFeed" />
  <div v-if="activeTab === 1"><Followers ref="followersTab" /></div>
  <div v-if="activeTab === 2"><MyFeed ref="myFeedTab" /></div>
</div>
```

#### 3C. Add refreshFeed method

Add this method to the `methods` object:
```js
refreshFeed() {
    // Re-fetch the active tab's data
    if (this.activeTab === 1 && this.$refs.followersTab) {
        this.$refs.followersTab.items = [];
        this.$refs.followersTab.currentPage = 1;
        this.$refs.followersTab.endReached = false;
        this.$refs.followersTab.fetchData();
    }
    if (this.activeTab === 2 && this.$refs.myFeedTab) {
        this.$refs.myFeedTab.items = [];
        this.$refs.myFeedTab.currentPage = 1;
        this.$refs.myFeedTab.endReached = false;
        this.$refs.myFeedTab.fetchData();
    }
},
```

**IMPORTANT:** The Followers and MyFeed components need `ref` attributes added to their divs (already shown above).

**Verify:** `npm run dev` — Feed page shows composer at the top, type buttons visible, clicking Image expands the form, clicking Trophy shows loading state.

---

### Step 4 — Remove Validate from sidebar

Open `resources/web/js/components/sidebar.vue`.

Find the `<li>` block that contains `<router-link to="/validate">` (around line 39-48). Comment it out or delete it entirely:

```html
<!-- Validate removed — functionality absorbed into Feed composer -->
<!--
<li>
    <router-link to="/validate" ...>
        ...
        <span>Validate</span>
    </router-link>
</li>
-->
```

**Verify:** `npm run dev` — Validate no longer appears in sidebar. Direct URL `/validate` still works (page file is kept).

---

### Step 5 — Check trophy endpoint

The composer calls `GET /api/forge/claimed` to list forged trophies. Verify this endpoint exists:

```bash
grep -n "claimed" routes/api.php
```

If it does NOT exist, check what endpoints exist under the forge prefix:
```bash
grep -n "forge" routes/api.php
```

And look at the ForgeController to find the correct endpoint for listing a user's forged/claimed trophies. The composer may need the URL adjusted in `FeedComposer.vue` method `fetchForgedTrophies`.

Common alternatives:
- `GET /api/forge` — might list available trophies
- `GET /api/forge/my-trophies` — user's forged trophies
- The TrophyRoom page likely already fetches this — check `TrophyRoom.vue` for the API call it uses

**If the endpoint needs adjustment:** Update ONLY the URL in `FeedComposer.vue` method `fetchForgedTrophies`. Do not create a new endpoint if one already exists.

---

### Step 6 — Deploy + verify

**Deploy:**
```bash
cd ~/Documents/trophyroom
git add -A
git commit -m "feat: Phase 4B — Feed composer + create-achievement endpoint + remove Validate from nav"
git push origin main
ssh -i ~/.ssh/id_ed25519_do_control root@164.92.83.95 "cd /var/www/ambar && git fetch origin main && git reset --hard origin/main && npm run build"
```

**Server-side:** The backend changes need no migration (no new DB columns). The existing `achievements` table has all needed fields.

**Verify on app.ambar.gg:**
1. Feed shows composer at the top
2. Click "Image" → form expands with category, upload, name, description
3. Click "Trophy" → shows list of forged trophies (or empty state)
4. Upload an image + fill fields → click Publish → post appears in feed
5. Video and Clip buttons show "soon" and are not clickable
6. Validate removed from sidebar
7. Existing feed posts still display correctly
8. Donations, comments, delete all still work

---

## Rules Reminder
1. One step at a time. Verify after each.
2. Vue 3 Options API.
3. Share Tech Mono only, weight 400.
4. Don't break existing endpoints or pages.
5. Don't modify AchievementService or AchievementController.
6. Deploy with `git reset --hard`, never `git pull`.
7. Backend changes: add new methods, never modify existing ones.
