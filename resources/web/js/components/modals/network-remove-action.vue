<template>
  <div class="modal_background" @click.self="closeModal">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">
      <h1 class="modal_header">
        {{ $store.state.networkRemoveUnfollowModal.title }}
      </h1>
      <h3 class="modal_label">
        Are you sure?
      </h3>
      <div class="modal_sign_up_with_buttons">
          <button class="main-button modal_sign_up_with_button" @click="sendRequest">
            <span>
            {{ $store.state.networkRemoveUnfollowModal.btn_text }}
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
      user_name: 'Name',
      action_text: 'Action text',
    }
  },
  methods: {
    closeModal() {
      store.state.networkRemoveUnfollowModal.show = false;
    },
    async sendRequest() {
      await api.post(store.state.networkRemoveUnfollowModal.action, {'id': store.state.networkRemoveUnfollowModal.user_id}).then(response => {
        if (response && response.data) {
          this.closeModal()
        }
      }).catch(error => {
        console.error('Network fetching data error:', error);
      });
    }
  }

}
</script>

<style scoped>

</style>