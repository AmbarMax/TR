<template>
  <div class="modal_background" @click="closeRevalidateModal">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeRevalidateModal" class="modal_close_button">
      <h1 class="modal_header">
        Your network will validate
      </h1>
      <h3 class="modal_label">
        You are going to need:
      </h3>
      <div class="point_need">
        {{ point_need }} Runes
        <img src="../../../../web/images/web/img/points/rune.svg" alt="rune">
      </div>
      <div class="point_current">
        Currently have <span class="number">{{ userRunesForValidate }}/{{ point_need }}</span>
      </div>
      <button-white
          @click="revalidateAchievement"
          :text="'Validate achievement now'"
          :disabled="userRunesForValidate < point_need"
          class="validate_achievement_with_button">
      </button-white>
      <h4 class="modal_small_label">
        Or
      </h4>
      <button-white  @click="revalidateSocialProof" :text="'Request runes from friends'" class="margin-fix"></button-white>
      <h4 class="modal_small_label">
        Revalidation could take some time
      </h4>
    </div>
  </div>
</template>

<script>

import buttonWhite from "../../parts/button.vue";
import CustomSelect from "../../parts/custom-select.vue";
import store from "../../store/store.js";
import api from "../../api/api.js";

export default {
  components: {CustomSelect, buttonWhite, store},
  props: [ 'img_link', 'text', 'achievement_data', 'achievement_button', 'icon_type', 'type', 'button_action', 'service', 'virtualHall'],
  data() {
    return {
      isDeleted: false,
      isActiveDotsDropdown: false,
      point_need: 5
    }
  },
  mounted() {
  },
  methods: {
    statusString() {
      if (this.achievement_data.status === 1) {
        return 'Validated';
      } else if (this.achievement_data.status === 2) {
        return 'Not validated';
      } else if (this.achievement_data.status === 3) {
        return 'Rejected';
      }
    },
    closeRevalidateModal() {
      store.state.revalidateModal.show = false;
    },
    revalidateAchievement() {
      api.post('/api/achievement/revalidate',{
        id: store.state.revalidateAch.id
      }).then(resp => {
        if (resp.status === 200) {
          store.state.notification = {
            message: 'Achievement successfully validate',
            type: 'success',
            show: true
          };
        }
        store.state.revalidateAch.status = 1;
      }).catch(e => {
        console.log('Revalidate', e)
      });
    },
    revalidateSocialProof() {
      store.state.revalidateSocialProofModal.show = true;
    }
  },
  computed: {
    userRunesForValidate() {
      if ( store.state.user.balances.rune >= this.point_need ) {
        return this.point_need;
      } else {
        return store.state.user.balances.rune;
      }
    }
  }
}

</script>


<style scoped>
.modal_window {
  padding: 40px 30px 60px 30px;
}

.point_current {
  margin-top: 30px;
  margin-bottom: 12px;
  color: rgba(255, 255, 255, 0.90);
  font-size: 20px;
  font-family: Orbitron;
  font-weight: 700;
  line-height: 28px;
}

.number {
  color: #FF0088;
}

.point_need {
  display: flex;
  align-items: center;
  text-align: center;
  gap: 8px;
  color: #FF0088;
  font-size: 16px;
  font-family: Orbitron;
  font-weight: 700;
  line-height: 22px;
  word-wrap: break-word
}

.margin-fix {
  margin-top: 12px !important;
}
</style>