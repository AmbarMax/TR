<template>
  <div class="modal_background">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" class="modal_close_button" @click="closeModal">
      <h1 class="modal_header">
        {{ $store.state.validateAchievementModal.title }}
      </h1>

      <div class="modal_sign_up_with_buttons">

        <h2 class="feed-card__title" v-if="achName">
          {{ achName }}
        </h2>
        <p class="feed-card__text" ref="cardText" v-if="achDesc">
          {{ achDesc }}
        </p>

        <div class="modal-image-wrapper">
          <img alt="modal-image" :src="`/storage/achievements/${this.achImage}`">
        </div>

        <div class="feed-card__count">
          <img src="../../../images/web/img/points/rune.svg" alt="runes">
          <button class="feed-card__operator" @click="increaseDecreaseAmbars('-')">
            <img src="../../../../../public/web/img/icons/green-minus.svg" alt="minus">
          </button>
          <span class="feed-card__count-value">
              {{ amount }}
          </span>
          <button class="feed-card__operator" @click="increaseDecreaseAmbars('+')">
            <img src="../../../../../public/web/img/icons/green-plus.svg" alt="green-plus">
          </button>
        </div>

        <button class="main-button modal_sign_up_with_button" @click="sendRequest" :disabled="disable_btn || !amount">
            <span>
            Validate and send runes
            </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import buttonWhite from "../../parts/button.vue";
import store from "../../store/store.js";
import api from "../../api/api.js";

export default {
  components: {
    buttonWhite
  },
  data() {
    return {
      disable_btn: false,
      amount: 0,
      achImage: null,
      achName: null,
      achDesc: null,
    }
  },
  methods: {
    closeModal() {
      store.state.validateAchievementModal.show = false;
    },
    async sendRequest() {
      if (!this.amount) {
        store.state.notification.type = 'failed';
        store.state.notification.message = 'Add runes';
      }
      await api.post('api/achievement/social-approve', {'id': store.state.validateAchievementModal.entity_id, 'amount': this.amount}).then(response => {
        if (response && response.data) {
          store.state.validateAchievementModal.entity_id = '';
          this.disable_btn = true;
          store.state.notification.type = 'success';
          store.state.notification.message = 'You sent the runes: '+this.amount;
          store.state.notification.show = true;
          this.closeModal();
        }
      }).catch(error => {
        console.error('Network fetching data error:', error);
      });
    },
    increaseDecreaseAmbars(operator) {
      if (operator === '+') {
        if (this.amount < 2){
          this.amount++
        }
      } else {
        if (this.amount > 0) {
          this.amount--
        }
      }
    },
    async getAchImage()
    {
      if (store.state.validateAchievementModal.entity_id) {
        await api.get('api/achievement/'+store.state.validateAchievementModal.entity_id+'/info').then(response => {
          if (response && response.data) {
            this.achImage = response.data.image;
            this.achName = response.data.name;
            this.achDesc = response.data.description;
          }
        }).catch(error => {
          console.error('Network fetching data error:', error);
        });
      }
    }
  },
  mounted(){
    this.getAchImage()
  }
}
</script>

<style scoped>
.modal-image-wrapper img{
  object-fit: cover;
  width: 100%;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>