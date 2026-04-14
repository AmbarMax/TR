<template>
  <ambar-notification v-if="notificationModalOpen"></ambar-notification>
  <div v-if="user !== null" class="main_block mobile_main_block" style="padding: 0!important;">
    <div class="profile_images_block mobile_margin_50 virtual_hall_background_image">
      <div class="profile_background_image">
        <div class="virtual_hall_background">
          <div v-if="!user.background"></div>
          <img v-else :src="user.background" alt="user_bg">
        </div>
      </div>
      <div class="virtual_hall_avatar">
        <div>
          <img v-if="!user.avatar" src="../../../images/web/img/user.svg" alt="user">
          <img v-else :src="user.avatar" alt="user">
        </div>
      </div>
    </div>
    <div class="virtual_hall_block">
      <div class="user_info">
        <div class="user_name">
          {{user.username}}
        </div>
        <div class="user_block">
          <div class="user_reward">
            <div class="description">
                {{user.description}}
            </div>

            <div class="user_buttons">
                <template v-if="followStatus">
                    <SubscriptionPricesBlock />

                    <button
                        class="mt-30"
                        :class="followStatus === follow ? 'followed_btn' : 'follow_btn' "
                        @click="followAction(user.id, user.username)"
                        :disabled="followBtnLoading"
                    >
                        {{ followStatus === follow ? 'Following' : 'Follow' }}
                    </button>
                </template>
                <button class="copy_link" @click="copyLink">
                    <img src="../../../images/web/img/icons/chain.svg" alt="ambar">
                    Copy Link
                </button>
            </div>
          </div>
          <div class="points">
            <div class="point">
              <div class="name">
                Uru
              </div>
              <div class="count">
                    <span>
                        {{ userBalances.uru }}
                    </span>
                <img src="../../../images/web/img/points/uru.svg" alt="uru">
              </div>
            </div>
            <div class="line"></div>
            <div class="point">
              <div class="name">
                Ambar
              </div>
              <div class="count">
                    <span>
                        {{ userBalances.ambar }}
                    </span>
                <img src="../../../images/web/img/points/ambar.svg" alt="ambar">
              </div>
            </div>
            <div class="line only_desktop"></div>
            <div class="point">
              <div class="name">
                Rune
              </div>
              <div class="count">
                    <span>
                        {{ userBalances.rune }}
                    </span>
                <img src="../../../images/web/img/points/rune.svg" alt="rune">
              </div>
            </div>
            <div class="line"></div>
            <div class="point">
              <div class="name">
                NFTs
              </div>
              <div class="count">
                    <span>
                        {{ userNFTs }}
                    </span>
              </div>
            </div>
          </div>

        </div>

      </div>
      <div class="achievements_block mobile_margin">
        <div class="achievements_header">
          <div class="name">
            <span>Trophies</span>
          </div>
        </div>
      </div>

      <div class="achievements_block" v-if="achievements.length">
        <div class="achievements_header">
          <div class="title">
            <span>Achievements</span>
          </div>
<!--          <div class="sorting">-->
<!--            <div class="label">-->
<!--              <span>Category</span>-->
<!--            </div>-->
<!--            <CustomSelect-->
<!--                :options="['All', 'Not all']"-->
<!--                class="All"-->
<!--            />-->
<!--          </div>-->
        </div>
        <div class="achievements_content">
          <validate-card v-for="index in achievements.length" :type="'trophy-room'" :key="index" :achievement_data="achievements[index - 1]" :achievement_button="button_text" :virtualHall="true"></validate-card>
        </div>
      </div>
      <div class="achievements_block" v-if="NFTs.length">
        <div class="achievements_header">
          <div class="title">
            <span>NFTs</span>
          </div>
        </div>
        <div class="achievements_content">
          <forge-card v-for="index in NFTs.length"
                      :key="index"
                      :achievement_data="NFTs[index - 1]"
                      :type="'weight'"
                      :page="'virtual-hall'"
                      :icon_type="'Ambar'">
          </forge-card>
        </div>
      </div>
      <div class="achievements_block">
        <div class="achievements_header" v-if="discord_badges.length || github_badges.length || steam_badges.length">
          <div class="title">
            <span>Badges</span>
          </div>
        </div>
        <div class="achievements_header mt-0" v-if="discord_badges.length">
          <div class="sub_title">
            <img src="../../../images/web/img/social_icons/ach_discord.svg" alt="discord">
            <span>Discord</span>
          </div>
        </div>
        <div class="achievements_content" v-if="discord_badges.length" :class="{ 'no-scroll': discord_badges.length === 1 }">
          <achievement-card v-for="index in discord_badges.length" :key="index" :achievement_data="discord_badges[index - 1]" :class="{ 'alone-element': discord_badges.length === 1 }" :service="'discord'"></achievement-card>
        </div>
      </div>
      <div class="achievements_block" v-if="github_badges.length">
        <div class="achievements_header mt-44">
          <div class="sub_title">
            <img src="../../../images/web/img/social_icons/ach_github.svg" alt="github">
            <span>Github</span>
          </div>
        </div>
        <div class="achievements_content" :class="{ 'no-scroll': github_badges.length === 1 }">
          <achievement-card v-for="index in github_badges.length" :key="index" :achievement_data="github_badges[index - 1]" :class="{ 'alone-element': github_badges.length === 1 }" :service="'github'"></achievement-card>
        </div>
      </div>
      <div class="achievements_block" v-if="steam_badges.length">
        <div class="achievements_header mt-44">
          <div class="sub_title">
            <img src="../../../images/web/img/social_icons/ach_steam.svg" alt="steam">
            <span>Steam</span>
          </div>
        </div>
        <div class="achievements_content" :class="{ 'no-scroll': steam_badges.length === 1 }">
          <achievement-card v-for="index in steam_badges.length" :key="index" :achievement_data="steam_badges[index - 1]" :class="{ 'alone-element': steam_badges.length === 1 }" :service="'steam'"></achievement-card>
        </div>
      </div>
      <div class="achievements_block" v-if="trophies.length">
        <div class="achievements_header">
          <div class="title">
            <span>Trophies</span>
          </div>
<!--          <div class="sorting">-->
<!--            <div class="label">-->
<!--              <span>Weight</span>-->
<!--            </div>-->
<!--            <CustomSelect-->
<!--                :options="['All', 'Not all']"-->
<!--                class="All"-->
<!--            />-->
<!--          </div>-->
        </div>
        <div class="achievements_content">
          <forge-card v-for="index in trophies.length"
                      :key="index"
                      :achievement_data="trophies[index - 1]"
                      :type="'weight'"
                      :page="'virtual-hall'"
                      :icon_type="'Ambar'">
          </forge-card>
        </div>
      </div>
    </div>
  </div>
  <div v-if="user === null" class="main__block_not-found" style="padding: 0!important;">
    <div class="user-not-found">
      <h2 class="user-not-found__header">
        User not found...
      </h2>
      <router-link to="/" class="copy_link">
        <button-white :text="'Go to the homepage'"></button-white>
      </router-link>
    </div>
  </div>
</template>

<script>

import {defineComponent} from "vue";
import achievementCard from "../../parts/achievement-card.vue";
import forgeCard from "../../parts/forge-card.vue";
import buttonWhite from "../../parts/button.vue";
import CustomSelect from "../../parts/custom-select.vue";
import api from "../../api/api.js";
import ambarNotification from "../../components/modals/ambar-notification.vue";
import store from "../../store/store.js";
import {Centrifuge} from "centrifuge";
import SubscriptionPricesBlock from "./components/SubscriptionPricesBlock.vue";
import validateCard from "../../parts/validate-card.vue";


export default defineComponent({
  components: {
    SubscriptionPricesBlock,
    CustomSelect,
    achievementCard,
    forgeCard,
    buttonWhite,
    ambarNotification,
    store,
    validateCard
  },
  data() {
    return {
      count_achievements: 0,
      activeTab: 1,
      import_text: "Import",
      achievements: [],
      NFTs: [],
      trophies: [],
      github_badges: [],
      steam_badges: [],
      discord_badges: [],
      button_text: "Validate",
      icon_type: "Round",
      user: {},
      followStatus: null,
      follow: 1,
      followBtnLoading: false,
      userBalances: {
        uru: 0,
        ambar: 0,
        rune: 0,
      },
      userNFTs: 0
    }
  },
  methods: {
    changeTab(tabNumber) {
      this.activeTab = tabNumber;
    },
    getUserData(username) {
      api.get(`/api/virtual-hall/${username}`).then(resp => {
        if (resp.status === 200) {
          this.followStatus = resp.data.followStatus;

          if (resp.data.user.data) {

              this.subscribeCentrifugoBalances(resp.data.user.data.id);

              this.user = resp.data.user.data;
            this.user.balances.forEach( (balance)=>{
              this.userBalances[balance.currency.name] = +(balance.amount);
            })

            if (resp.data.user.data.avatar && resp.data.user.data.avatar != '/images/avatar/default-profile-img.png'){
              this.user.avatar = resp.data.user.data.avatar;
            }
            if (resp.data.user.data.background && resp.data.user.data.background != '/images/background/default-background-img.png'){
              this.user.background = resp.data.user.data.background;
            }
            if (resp.data.user.data.background && resp.data.user.data.background != '/images/background/default-background-img.png'){
              this.user.background = resp.data.user.data.background;
            }

            this.github_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'github');
            this.steam_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'steam');
            this.discord_badges = resp.data.user.data.badges.data.filter(item => item.integration === 'discord');

            const allTrophies = resp.data.user.data.trophies;
            this.trophies = allTrophies.filter(item => item.is_nft === 0);
            this.NFTs = allTrophies.filter(item => item.is_nft === 1);
            this.userNFTs = this.NFTs.length;
            this.achievements = resp.data.user.data.achievements;
          } else {
            this.user = null;
          }
        }
      })
    },
    copyLink() {
      navigator.clipboard.writeText(window.location.href);
      store.state.notification = {
        message: 'Link copied to clipboard.',
        type: 'info',
        show: true
      }
    },
    followAction(userId, userName) {

      this.followBtnLoading = true;
      api.post(`/api/follow/action`, {id: userId}).then(resp => {
        if (resp && resp.data){
          this.followStatus = resp.data.followStatus;
          let msg = this.followStatus === this.follow ? `You follow to ${ userName }` : `You unfollow to ${ userName }`
          store.state.notification = {
            message: msg,
            type: 'info',
            show: true
          }
        }

      }).catch(error => {
        console.log('Error in followAction:', error);
      }).finally(() => {
        setTimeout(() => {
          this.followBtnLoading = false;
        }, 1000);
      });
    },
    async subscribeCentrifugoBalances(userId){
      this.centrifuge = new Centrifuge(localStorage.getItem('websocket_url'));
      this.centrifuge.setToken(localStorage.getItem('centrifugo_token'));

      const self = this;
      this.centrifuge.newSubscription('ambar-balance-ambar-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.ambar = Math.floor(ctx.data.amount);
          }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-uru-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.uru = Math.floor(ctx.data.amount);
          }).subscribe();

      this.centrifuge.newSubscription('ambar-balance-rune-' + userId)
          .on('publication', function(ctx) {
            self.userBalances.rune = Math.floor(ctx.data.amount);
          }).subscribe();

      // let authUser = JSON.parse(localStorage.getItem('user'));
      // while (!authUser) {
      //   await new Promise(resolve => setTimeout(resolve, 250));
      //   authUser = JSON.parse(localStorage.getItem('user'));
      // }

      this.centrifuge.newSubscription('network-' + userId)
          .on('publication', function(action) {
            if (action.data.userId === userId) {
              self.followStatus = action.data.type;
            }
          }).subscribe();

      this.centrifuge.connect();
    },
  },
  mounted() {
    this.getUserData(this.$route.params.username);
  },
  computed: {
    notificationModalOpen: function () {
      return store.state.notification.show;
    },
  }
})
</script>

<style scoped>
.mt-44 {
  margin-top: 44px!important;
}
.no-scroll {
    overflow-x: auto;
}
@media (min-width: 968px) and (max-width: 1268px) {
    .virtual_hall_block .points {
        margin-left: -100px;
    }
}

</style>
