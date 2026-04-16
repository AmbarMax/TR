<template>
  <div class="modal_background" @click.self="closeSocialProofAchievement">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeSocialProofAchievement" class="modal_close_button">
      <h1 class="modal_header">
        Runes request
      </h1>
      <h3 class="modal_label">
        Choose users to ask for runes:
      </h3>

      <h4 v-if="!users" class="modal_label">
        You have no followers!
      </h4>

      <div class="my-vault-tab">
        <div class="list-wrapper-user-list mt-30 message-notification__list" ref="scrollable" @scroll="handleScroll" v-if="users">
          <div @click="handleSelectUser(item.id)" class="item notification-message" v-for="item in users" :key="item.id" :class="{ 'selected-user': selectedUser.includes(item.id) }">
            <div class="left-part">
              <img v-if="!item.avatar" src="../../../images/web/img/user.svg" alt="user">
              <img v-else :src="'storage/users/' + item.id + '/avatar/' + item.avatar +'.jpg'" alt="user">
              <span>
                   <span class="email-title">{{ item.email }}</span>
                   <span class="name-title">{{ item.name }}</span>
                </span>
            </div>
          </div>
        </div>
      </div>

      <h3 class="modal_label" style="margin-top: 30px">
        You are going to need:
      </h3>
      <div class="point_need">
        200 Ambars
        <img src="../../../../web/images/web/img/points/ambar.svg" alt="rune">
      </div>
      <div class="point_current">
        You currently have <span class="number">{{ userAmbarForValidate }}/ 200</span>
      </div>

      <button-white
          @click="validateAchivement()" :disabled="selectedUser === [] || button_is_locked" :text="'Request runes'" class="validate_achievement_social_proof"></button-white>
    </div>
  </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import UserSelectList from "../UserSelectList.vue";
import api from "../../api/api.js";
export default {
  components: {
    UserSelectList,
    buttonWhite
  },
  data() {
    return {
      create_button_text: 'Create Achievement',
      name: '',
      description: '',
      selectedImage: null,
      selectedImageFile: null,
      users: [],
      selectedUser: [],
      button_is_locked: false,
      currentPage: 1,
      endReached: false,
      total: 0,
      useAmbarForValidate: 0,
    }
  },
  methods: {
    closeNetworkValidate() {
      store.state.SocialProofValidateModalOpen = false;
      store.state.modals.createAchievement.data = {};
    },
    getNetworkUsers() {
      api.get('/api/follow/followers?page=' + this.currentPage).then(response => {
        if (response && response.data) {
          const newItems = response.data[0].data;
          store.state.followings = response.data[0].data;
          this.total = response.data[0].total;
          if (newItems.length === 0) {
            this.endReached = true;
          } else {
            this.users = [...this.users, ...newItems];
            store.state.followings.data = this.users;
            this.page++;
          }
        }
      }).catch(error => {
        console.error('Network fetching data error:', error);
      });
    },
    handleSelectUser(id) {
      const index = this.selectedUser.indexOf(id);
      if (index !== -1) {
        this.selectedUser.splice(index, 1);
      } else {
        this.selectedUser.push(id);
      }
    },
    validateAchivement(){
      if (this.selectedUser.length !== 0){
        this.button_is_locked = true;
        // store.state.modals.createAchievement.data.proofUserId = Object.values(this.selectedUser);
        api.post('/api/achievement/revalidate-social', {id: store.state.revalidateAch.id, proofUserId: Object.values(this.selectedUser)})
            .then( resp => {
              if (resp.status === 200) {
                this.closeNetworkValidate();
                store.state.notification = {
                  message: resp.data.message,
                  type: 'success',
                  show: true
                };
                this.$store.commit('updateDataOnValidationPage');
              }
            });

        this.closeSocialProofAchievement();
      }

    },
    closeSocialProofAchievement(){
      store.state.revalidateModal.show = false;
      store.state.revalidateSocialProofModal.show = false;
    },
    handleScroll() {
      const scrollContainer = this.$refs.scrollable;
      if (scrollContainer.scrollTop + scrollContainer.clientHeight === scrollContainer.scrollHeight
          && this.users
          && this.users.length < this.total
      ) {
        this.currentPage++;
        this.getNetworkUsers();
      }
    },
    SelectUser(id){
      // this.selectUser = id;
      store.state.modals.createAchievement.data.proofUserId = id;
    },
  },
  mounted() {
    this.getNetworkUsers();
  },

  computed: {
    userAmbarForValidate() {
      if ( store.state.user.balances.ambar >= 200 ) {
        return 200;
      } else {
        this.button_is_locked = true;
        return store.state.user.balances.ambar;
      }
    }
  }

}
</script>

<style scoped>
.message-notification__list{

}
.validate_achievement_social_proof {
  margin-top: 30px;
}
.list-wrapper-user-list {
  background: #303135;
  width: 100%;
  max-height: 300px;
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.item {
  background: rgba(186, 186, 186, 0.15);
}


.selected-user {
  border: 1px solid #CAFB01!important;
}

.item {
  display: flex;
  justify-content: space-between;
  padding: 18px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.left-part {
  display: flex;
  align-items: center;
}

.left-part img {
  margin-right: 10px;
  cursor: pointer;
}

.left-part span {
  display: block;
}

img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
}

.email-title,
.name-title{
  font-family: 'Share Tech Mono', monospace;
}

.email-title,
.name-title {
  color: white;
  cursor: pointer;
}

.name-title {
  color: #BABABA;
  padding-top: 10px;
  font-size: 16px;
}

@media (max-width: 1065px) {
  .item{
    flex-direction: column;
  }
}

@media (max-width: 371px) {
  .email-title,
  .name-title
  {
    word-break: break-word;
  }
}

.list-wrapper-user-list:hover {
  cursor: pointer;
}

.point_current {
  margin-top: 30px;
  margin-bottom: 12px;
  color: rgba(255, 255, 255, 0.90);
  font-size: 20px;
  font-family: 'Share Tech Mono', monospace;
  font-weight: 700;
  line-height: 28px;
}

.number {
  color: #cafb01;
}

.point_need {
  display: flex;
  align-items: center;
  text-align: center;
  gap: 8px;
  color: #cafb01;
  font-size: 16px;
  font-family: 'Share Tech Mono', monospace;
  font-weight: 700;
  line-height: 22px;
  word-wrap: break-word
}
</style>
