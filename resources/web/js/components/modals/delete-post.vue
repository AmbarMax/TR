<template>
  <div class="modal_background" @click.self="closeModal">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">
      <h1 class="modal_header">
        {{ $store.state.deletePostModal.title }}
      </h1>
      <h3 class="modal_label">
        Are you sure?
      </h3>
      <div class="modal_sign_up_with_buttons">
        <button class="main-button modal_sign_up_with_button" @click="deletePostRequest">
            <span>
            {{ $store.state.deletePostModal.btn_text }}
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
  computed: {
    isStaff() {
      if (this.$store.state.user?.roles?.length > 0) {
        return this.$store.getters.isStaff;
      }
      try {
        const stored = JSON.parse(localStorage.getItem('user') || '{}');
        return !!stored.is_staff_legacy;
      } catch {
        return false;
      }
    },
  },
  methods: {
    closeModal() {
      store.state.deletePostModal.show = false;
    },
    async deletePostRequest() {
      if (this.isStaff) {
        await api.post('/api/feed/destroy', {'id': store.state.deletePostModal.post_id}).then(response => {
          if (response && response.data) {
            const post = store.state.posts.findIndex((obj) => obj.id === store.state.deletePostModal.post_id);
            if (post > -1) {
              store.state.posts.splice(post, 1);
              store.state.postsTotal -=1;
            }
              const postMy = store.state.myPosts.findIndex((obj) => obj.id === store.state.deletePostModal.post_id);
              if (postMy > -1) {
                  store.state.myPosts.splice(postMy, 1);
                  store.state.myPostsTotal -=1;
              }
            this.closeModal()
          }
        }).catch(error => {
          console.error('Delete post Moderator fetching data error:', error);
        });
      } else {
        await api.post('/api/feed/remove', {'id': store.state.deletePostModal.post_id}).then(response => {
          if (response && response.data) {
            const post = store.state.myPosts.findIndex((obj) => obj.id === store.state.deletePostModal.post_id);
            if (post > -1) {
              store.state.myPosts.splice(post, 1);
              store.state.myPostsTotal -=1;
            }
            this.closeModal()
          }
        }).catch(error => {
          console.error('Delete post fetching data error:', error);
        });
      }
    }
  }
}
</script>

<style scoped>

</style>
