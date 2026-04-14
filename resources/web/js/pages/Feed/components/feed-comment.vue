<template>
    <div class="feed-comment">
      <img class="feed-comment__avatar" v-if="!comment.creator.avatar" src="../../../../images/web/img/user.svg" alt="comment-avatar" @click="navigateToVirtualHall(comment.creator.username)">
      <img class="feed-comment__avatar" v-else :src="comment.creator.avatar" alt="user" @click="navigateToVirtualHall(comment.creator.username)">
        <div class="feed-comment__data">
            <span class="feed-comment__name" @click="navigateToVirtualHall(comment.creator.username)">
                {{ comment.creator.username }}
            </span>
            <p class="feed-comment__message">
                {{ comment.body }}
            </p>
            <span  class="feed-comment__date">
                {{ comment.created_at }}
            </span>
        </div>

      <button class="feed-card__delete" v-if="this.isModerator() === true" @click="openDeleteCommentModal(comment.id)">
        <img src="../../../../images/web/img/icons/trash.svg" alt="trash-icon">
      </button>
    </div>
</template>

<script>

import store from "../../../store/store.js";

export default {
    props: ['comment', 'post'],
    data() {
        return {

        }
    },
    methods: {
      navigateToVirtualHall(username) {
        window.open(`/virtual-hall/${username}`, '_blank');
      },
      isModerator() {
        let authUser = JSON.parse(localStorage.getItem('user'));
        while (!authUser) {
          new Promise(resolve => setTimeout(resolve, 250));
          authUser = JSON.parse(localStorage.getItem('user'));
        }

        if (authUser && authUser.roles) {
          return authUser.roles.some(role => role.name === 'Master user');
        }else{
          if (store.state.user.roles && store.state.user.roles.length > 0) {

            const moderatorRole = store.state.user.roles.find(role => role.name === 'Master user');

            return !!moderatorRole;
          } else {
            return false;
          }
        }
      },
      openDeleteCommentModal(commentId) {

        store.state.deleteCommentModal = {
          title: 'Delete this comment?',
          btn_text: 'Delete',
          commentId: commentId,
          show: true,
          postId: this.post
        }
      },
    },
}
</script>

<style scoped>
.feed-comment__avatar, .feed-comment__name{
  cursor: pointer;
}
</style>
