<template>
  <div class="main_block">
    <!-- Header -->
    <div class="tr-page-header">
      <div>
        <h1 class="tr-page-title">Trophy Room</h1>
        <p class="tr-page-subtitle">Your vault. Manage badges, forge trophies, curate your Virtual Hall.</p>
      </div>
      <button class="tr-vhall-btn" @click="RedirectToVirtualHall">
        Virtual Hall preview →
      </button>
    </div>

    <!-- Tabs -->
    <div class="tr-tabs">
      <div
        class="tr-tab"
        :class="{ 'tr-tab--active': activeTab === 1 }"
        @click="changeTab(1)"
      >My vault</div>
      <div
        class="tr-tab"
        :class="{ 'tr-tab--active': activeTab === 2 }"
        @click="changeTab(2)"
      >Showcase</div>
    </div>

    <!-- My Vault Tab -->
    <div v-if="activeTab === 1" class="tr-vault">

      <!-- Forged Trophies -->
      <div class="tr-section" v-if="trophies.length || trophiesLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Forged trophies</span>
          <span class="tr-section-count">{{ trophies.length }} trophies</span>
        </div>
        <Loader v-if="trophiesLoading" />
        <div class="tr-trophy-grid" v-else>
          <TrophyCard
            v-for="trophy in trophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="true"
          />
        </div>
      </div>

      <!-- Custom Achievements -->
      <div class="tr-section" v-if="my_achievements.length || achievementLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Custom achievements</span>
          <span class="tr-section-count">{{ my_achievements.length }}</span>
        </div>
        <Loader v-if="achievementLoading" />
        <div class="tr-ach-list" v-else>
          <AchievementRow
            v-for="ach in my_achievements"
            :key="ach.id"
            :achievement="ach"
          />
        </div>
      </div>

      <!-- Platform Badges -->
      <div class="tr-section" v-if="discord_achievements.length || github_achievements.length || steam_achievements.length || badgesLoading">
        <div class="tr-section-header">
          <span class="tr-section-label">Platform badges</span>
        </div>
        <Loader v-if="badgesLoading" />
        <template v-else>
          <!-- Discord -->
          <div class="tr-platform-group" v-if="discord_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/discord.svg" alt="Discord" class="tr-platform-icon" />
              <span class="tr-platform-name">Discord</span>
              <span class="tr-platform-count">{{ discord_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in discord_achievements"
                :key="badge.id"
                :badge="badge"
                service="discord"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('discord')">Import Discord</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('discord')">Import Discord badges</button>
          </div>

          <!-- Steam -->
          <div class="tr-platform-group" v-if="steam_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/steam.svg" alt="Steam" class="tr-platform-icon" />
              <span class="tr-platform-name">Steam</span>
              <span class="tr-platform-count">{{ steam_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in steam_achievements"
                :key="badge.id"
                :badge="badge"
                service="steam"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('steam')">Import Steam</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('steam')">Import Steam badges</button>
          </div>

          <!-- GitHub -->
          <div class="tr-platform-group" v-if="github_achievements.length">
            <div class="tr-platform-header">
              <img src="../../../web/images/web/img/icons/github.svg" alt="GitHub" class="tr-platform-icon" />
              <span class="tr-platform-name">GitHub</span>
              <span class="tr-platform-count">{{ github_achievements.length }}</span>
            </div>
            <div class="tr-badge-grid">
              <BadgeTile
                v-for="badge in github_achievements"
                :key="badge.id"
                :badge="badge"
                service="github"
              />
            </div>
            <button class="tr-import-btn" @click="ImportData('github')">Import GitHub</button>
          </div>
          <div v-else class="tr-platform-group">
            <button class="tr-import-btn" @click="ImportData('github')">Import GitHub badges</button>
          </div>
        </template>
      </div>

      <!-- Empty state -->
      <div v-if="!trophies.length && !my_achievements.length && !discord_achievements.length && !steam_achievements.length && !github_achievements.length && !trophiesLoading && !achievementLoading && !badgesLoading" class="tr-empty-state">
        <p class="tr-empty-text">Your vault is empty. Import badges from Discord, Steam, or GitHub to get started.</p>
      </div>
    </div>

    <!-- Showcase Tab -->
    <div v-if="activeTab === 2" class="tr-vault">
      <div class="tr-section">
        <div class="tr-section-header">
          <span class="tr-section-label">Showcased items</span>
        </div>
        <p class="tr-empty-text" v-if="!showcasedTrophies.length && !showcasedAchievements.length">
          Nothing showcased yet. Go to My Vault and click "Showcase" on items you want to display in your Virtual Hall.
        </p>
        <div class="tr-trophy-grid" v-if="showcasedTrophies.length">
          <TrophyCard
            v-for="trophy in showcasedTrophies"
            :key="trophy.id"
            :trophy="trophy"
            :show-forge-button="false"
            :show-showcase="true"
          />
        </div>
        <div class="tr-ach-list" v-if="showcasedAchievements.length" style="margin-top: 20px;">
          <AchievementRow
            v-for="ach in showcasedAchievements"
            :key="ach.id"
            :achievement="ach"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import { defineComponent } from "vue";
import achievementCard from "../parts/achievement-card.vue";
import buttonWhite from "../parts/button.vue";
import CustomSelect from "../parts/custom-select.vue";
import store from "../store/store.js";
import api from "../api/api.js";
import forgeCard from "../parts/forge-card.vue";
import ValidateCard from "../parts/validate-card.vue";
import Loader from "../components/Loader.vue";
import TrophyCard from "../parts/trophy-card.vue";
import BadgeTile from "../parts/badge-tile.vue";
import AchievementRow from "../parts/achievement-row.vue";

export default defineComponent({
    components: {
        Loader,
        ValidateCard,
        CustomSelect,
        achievementCard,
        buttonWhite,
        store,
        forgeCard,
        TrophyCard,
        BadgeTile,
        AchievementRow,
    },
    data() {
        return {
            count_achievements: 0,
            activeTab: 1,
            currentPage: 1,
            totalPages: 3,
            import_text: "Import",
            showTestData: true,
            trophies: [],
            keys: [],
            NFTs: [],
            achievements: [],
            github_achievements: [],
            steam_achievements: [],
            discord_achievements: [],
            button_text: "Showcase",
            icon_type: "Ambar",
            my_achievements: [],
            badgesLoading: false,
            trophiesLoading: false,
            keysLoading: false,
            achievementLoading: false
        }
    },
    methods: {
        changeTab(tabNumber) {
            this.activeTab = tabNumber;
        },
        importBudgesModalOpen(){
            store.state.importBudgesModalOpen = true;
        },
        RedirectToVirtualHall(){
            window.open(`/virtual-hall/${store.state.userUsername}`, '_blank');
        },
        changePage(newPage) {
            this.currentPage = newPage;
        },
        ImportData(type) {
            const token = localStorage.getItem('access_token');
            window.location.href = '/login/' + type + '?token=' + encodeURIComponent(token);
            store.state.loaderStatus = true;
        },
        getAchievements() {
            this.achievementLoading = true;
            api.get('/api/achievement?status=1').then( resp => {
                if (resp.status === 200) {
                    this.my_achievements = resp.data.data;
                }
            }).catch(e => {
              console.log('achievement', e);
            }).finally(() => {
              this.achievementLoading = false;
            });
        },
        getKeys() {
            this.keysLoading = true;
            api.get('/api/keys').then( resp => {
                if (resp.status === 200) {
                    this.keys = resp.data;
                }
            }).catch(e => {
                console.log('keys', e);
            }).finally(() => {
                this.keysLoading = false;
            });
        }
    },
    mounted(){
        this.badgesLoading = true;
        this.trophiesLoading = true;
        this.keysLoading = true;
        this.showTestData = store.state.showTestData;
        this.totalPages = Math.ceil(this.achievements.length / 3);
        this.getKeys();
        api.get('/api/badges').then( resp => {
            if (resp.status === 200) {
                const data = resp.data.data;
                this.count_achievements = data.length;
                this.discord_achievements = data.filter(achievement => achievement.integration === 'discord');
                this.steam_achievements   = data.filter(achievement => achievement.integration === 'steam');
                this.github_achievements  = data.filter(achievement => achievement.integration === 'github');
            }
        }).catch(e => {
          console.log('badges', e)
        }).finally(() => {
          this.badgesLoading = false;
        });
        api.get('/api/forge/trophies').then( resp => {
            if (resp.status === 200) {
              const allTrophies = resp.data.trophies;
              this.trophies = allTrophies.filter(trophy => trophy.is_nft === 0);
              this.NFTs     = allTrophies.filter(trophy => trophy.is_nft === 1);
            }
        }).catch(e => {
          console.log('trophies', e)
        }).finally(() => {
          this.trophiesLoading = false;
        });
        this.getAchievements();
    },
    computed: {
        displayedAchievements() {
            const startIndex = (this.currentPage - 1) * 3;
            const endIndex = startIndex + 3;
            return this.achievements.slice(startIndex, endIndex);
        },
        showcasedTrophies() {
            return this.trophies.filter(t => t.pivot && t.pivot.display);
        },
        showcasedAchievements() {
            return this.my_achievements.filter(a => a.display);
        },
        showcasedBadges() {
            const all = [...this.discord_achievements, ...this.steam_achievements, ...this.github_achievements];
            return all.filter(b => b.display);
        },
    }
})
</script>

<style scoped>
.tr-page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  gap: 20px;
  padding-top: 32px;
}

.tr-page-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 28px;
  font-weight: 400;
  margin: 0 0 6px;
}

.tr-page-subtitle {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  margin: 0;
}

.tr-vhall-btn {
  background: transparent;
  border: 1px solid #ff6100;
  color: #ff6100;
  border-radius: 4px;
  padding: 8px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
  flex-shrink: 0;
}

.tr-vhall-btn:hover {
  background: rgba(255, 97, 0, 0.1);
}

.tr-tabs {
  display: flex;
  gap: 0;
  border-bottom: 1px solid #2a2c2e;
  margin-bottom: 28px;
}

.tr-tab {
  padding: 10px 20px;
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: color 0.15s, border-color 0.15s;
}

.tr-tab--active {
  color: #c1f527;
  border-bottom-color: #c1f527;
}

.tr-tab:hover:not(.tr-tab--active) {
  color: #feeddf;
}

.tr-vault {
  padding-bottom: 60px;
}

.tr-section {
  margin-bottom: 36px;
}

.tr-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.tr-section-label {
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.tr-section-count {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.tr-trophy-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
}

.tr-ach-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tr-platform-group {
  margin-bottom: 24px;
}

.tr-platform-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}

.tr-platform-icon {
  width: 20px;
  height: 20px;
  opacity: 0.7;
}

.tr-platform-name {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
}

.tr-platform-count {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

.tr-badge-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.tr-import-btn {
  background: transparent;
  border: 1px solid #2a2c2e;
  color: #9a9590;
  border-radius: 4px;
  padding: 8px 16px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.tr-import-btn:hover {
  border-color: #9a9590;
  color: #feeddf;
}

.tr-empty-state {
  padding: 60px 0;
  text-align: center;
}

.tr-empty-text {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  text-align: center;
  padding: 40px 20px;
}

@media (max-width: 640px) {
  .tr-page-header {
    flex-direction: column;
  }

  .tr-trophy-grid {
    grid-template-columns: 1fr;
  }
}
</style>
