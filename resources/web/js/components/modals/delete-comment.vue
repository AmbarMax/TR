<template>
  <div class="modal_background" @click.self="closeModal">
    <div class="modal_window">
      <img src="../../../../web/images/web/img/icons/close.svg" alt="close" @click="closeModal" class="modal_close_button">
      <h1 class="modal_header">
        {{ $store.state.deleteCommentModal.title }}
      </h1>
      <h3 class="modal_label">
        Are you sure?
      </h3>
      <div class="modal_sign_up_with_buttons">
        <button class="main-button modal_sign_up_with_button" @click="deletePostRequest">
            <span>
            {{ $store.state.deleteCommentModal.btn_text }}
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
      store.state.deleteCommentModal.show = false;
    },
    async deletePostRequest() {
      if (this.isStaff) {

        await api.post('/api/feed/comments/destroy', {'id': store.state.deleteCommentModal.commentId, 'postId': store.state.deleteCommentModal.postId}).then(response => {
          if (response && response.data) {
            const post = store.state.posts.find(post => post.id === store.state.deleteCommentModal.postId);
            const postMy = store.state.myPosts.find(post => post.id === store.state.deleteCommentModal.postId);

            let comments = response.data.post.comments;
            comments.forEach(comment => {
              comment.creator = comment.user;
              delete comment.user;
            });

            if (postMy != null){
              postMy.comments = comments;
              postMy.comments_count = comments.length;
            }

            post.comments = comments;
            post.comments_count = comments.length;

            this.closeModal()
          }
        }).catch(error => {
          console.error('Delete post Moderator fetching data error:', error);
        });

      } else {
        console.error('You not moderator');
      }
    }
  }
}
</script>

<style scoped>

</style>
