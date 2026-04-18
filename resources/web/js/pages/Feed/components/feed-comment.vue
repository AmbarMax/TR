<template>
  <div class="fco">
    <img class="fco__avatar" v-if="!comment.creator.avatar" src="../../../../images/web/img/user.svg" alt="comment-avatar" @click="navigateToVirtualHall(comment.creator.username)">
    <img class="fco__avatar" v-else :src="comment.creator.avatar" alt="user" @click="navigateToVirtualHall(comment.creator.username)">
    <div class="fco__body">
      <span class="fco__name" @click="navigateToVirtualHall(comment.creator.username)">{{ comment.creator.username }}</span>
      <p class="fco__text">{{ comment.body }}</p>
      <span class="fco__date">{{ comment.created_at }}</span>
    </div>
    <button class="fco__delete" v-if="this.isModerator() === true" @click="openDeleteCommentModal(comment.id)">
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
.fco {
  display: flex;
  gap: 10px;
  padding: 10px 0;
  border-bottom: 1px solid #1a1c1f;
  align-items: flex-start;
}

.fco__avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  cursor: pointer;
  border: 1px solid #2a2c2e;
}

.fco__body {
  flex: 1;
  min-width: 0;
}

.fco__name {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  cursor: pointer;
  display: block;
  margin-bottom: 2px;
}

.fco__name:hover {
  color: #c1f527;
}

.fco__text {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  line-height: 1.4;
  margin: 0 0 4px;
}

.fco__date {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px;
}

.fco__delete {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  opacity: 0.4;
  transition: opacity 0.15s;
  flex-shrink: 0;
}

.fco__delete:hover {
  opacity: 1;
}
</style>
