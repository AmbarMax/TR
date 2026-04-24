<template>
  <div class="fco">
    <img class="fco__avatar" v-if="!comment.creator.avatar" src="../../../../images/web/img/user.svg" alt="comment-avatar" @click="navigateToVirtualHall(comment.creator.username)">
    <img class="fco__avatar" v-else :src="comment.creator.avatar" alt="user" @click="navigateToVirtualHall(comment.creator.username)">
    <div class="fco__body">
      <span class="fco__name" @click="navigateToVirtualHall(comment.creator.username)">{{ comment.creator.username }}</span>
      <p class="fco__text">{{ comment.body }}</p>
      <span class="fco__date">{{ comment.created_at }}</span>
    </div>
    <button class="fco__delete" v-if="isStaff" @click="openDeleteCommentModal(comment.id)">
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
      navigateToVirtualHall(username) {
        window.open(`/virtual-hall/${username}`, '_blank');
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

<style lang="scss" scoped>
.fco {
  display: flex;
  gap: 10px;
  padding: 10px 12px;
  background: rgba(14, 15, 17, 0.5);
  border: 1px solid rgba(42, 44, 46, 0.5);
  align-items: flex-start;
  transition: border-color 0.15s;
}
.fco:hover { border-color: rgba(255, 97, 0, 0.15); }

.fco__avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  cursor: pointer;
  border: 1px solid var(--border);
  background: var(--surface-2);
}

.fco__body {
  flex: 1;
  min-width: 0;
}

.fco__name {
  color: var(--text);
  font-family: var(--mono);
  font-size: 12px;
  letter-spacing: 0.03em;
  cursor: pointer;
  display: block;
  margin-bottom: 3px;
  transition: color 0.15s;
}
.fco__name:hover { color: var(--primary); }

.fco__text {
  color: var(--text-muted);
  font-family: var(--mono);
  font-size: 12px;
  line-height: 1.6;
  letter-spacing: 0.02em;
  margin: 0 0 5px;
}

.fco__date {
  color: var(--text-dim);
  font-family: var(--mono);
  font-size: 10px;
  letter-spacing: 0.1em;
}

.fco__delete {
  background: transparent;
  border: 1px solid transparent;
  cursor: pointer;
  padding: 4px 6px;
  display: flex;
  align-items: center;
  transition: all 0.15s;
  flex-shrink: 0;
  color: var(--text-dim);
}
.fco__delete img { opacity: 0.5; transition: opacity 0.15s; }
.fco__delete:hover { border-color: rgba(226, 75, 74, 0.3); }
.fco__delete:hover img { opacity: 1; filter: hue-rotate(-20deg); }
</style>
