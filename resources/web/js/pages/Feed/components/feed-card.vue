<template>
  <div class="post" :id="item.id+'feed-dom-obj'">

    <!-- Header -->
    <div class="post-header">
      <div class="post-avatar" @click="navigateToVirtualHall(item.creator.username)">
        <img v-if="!item.creator.avatar" src="../../../../images/web/img/user.svg" alt="post-user">
        <img v-else :src="item.creator.avatar" alt="user">
      </div>
      <div class="post-user-info">
        <div class="post-username" @click="navigateToVirtualHall(item.creator.username)">{{ item.creator.username }}</div>
        <div class="post-timestamp">{{ item.created_at }}</div>
      </div>
      <button class="post-delete" v-if="myFeed || this.isModerator() === true" @click="openDeletePostModal(item.id)">
        Delete
      </button>
    </div>

    <!-- Post entity (image, trophy, or achievement art) -->
    <div class="post-image">
      <img :src="getImageUrl()" alt="post-image">
      <div class="post-image-caption">
        <div class="post-image-title">{{ item.entity.name }}</div>
        <p class="post-image-desc" ref="cardText">{{ item.entity.description }}</p>
        <div class="read-more-toggle" v-if="item.entity.description && item.entity.description.length > 150" @click="readMoreToggle">
          <span>{{ moreButton }}</span>
          <img src="../../../../images/web/img/icons/arrow-down.svg" alt="arrow" ref="arrowDown" class="read-more-arrow">
        </div>
      </div>
    </div>

    <!-- Actions -->
    <!-- Default state -->
    <div class="post-actions" v-if="actionStatuses.default">
      <button class="pact donate-btn" v-if="!myFeed" @click="addAmbars">
        <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="pact-icon">
        <span class="pact-count">{{ item.donations }}</span>
        <span>Donate</span>
      </button>
      <div class="pact pact-static" v-else>
        <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="pact-icon">
        <span class="pact-count">{{ item.donations }}</span>
        <span>{{ item.donations_count }} donated</span>
      </div>
    </div>

    <!-- Send state -->
    <div class="donate-send" v-if="actionStatuses.send">
      <div class="donate-amount">
        <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="pact-icon">
        <button @click="increaseDecreaseAmbars('-')">−</button>
        <span class="donate-amount-val">{{ data.balance }}</span>
        <button @click="increaseDecreaseAmbars('+')">+</button>
      </div>
      <button class="donate-send-btn" @click="sendAmbars(item.id, data.balance)">Send</button>
      <button class="donate-cancel-btn" @click="closeAmbarsAdd">Cancel</button>
    </div>

    <!-- Success state -->
    <div class="donate-send" v-if="actionStatuses.sendSuccess">
      <div class="donate-amount">
        <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="pact-icon">
        <span class="donate-amount-val">{{ data.balance }}</span>
      </div>
      <span class="donate-status">
        {{ item.donations_count === 1 || item.donations_count === 0
          ? 'You donated Ambars'
          : `You and ${item.donations_count} people donated Ambars` }}
      </span>
    </div>

    <!-- Comments -->
    <div class="post-comments" :ref="'scrollable_'+index" :class="{'scroll-height': item.comments_count > 3}" @scroll="handleScroll">
      <feed-comment v-for="comment of item.comments" :comment="comment" :post="item.id"></feed-comment>
    </div>
    <button v-if="item.comments_count > 3" class="post-show-more">
      <span v-if="checkShowMoreComments(item)" @click="showMoreComments(item.id)">Show more comments ({{ item.comments_count }})</span>
      <span v-if="checkTotalCommentsText(item)">Total ({{ item.comments_count }})</span>
    </button>

    <!-- New comment -->
    <div class="post-new-comment">
      <img :src="getAvatar()" alt="user-avatar" class="post-new-comment-avatar">
      <div class="post-new-comment-input">
        <textarea
          ref="textarea"
          @input="changeHeight"
          v-model="newMessage"
          placeholder="Leave comment..."
          @keydown="event => handleEnterMessage(event, item.id)"
        ></textarea>
        <button @click="sendMessage(item.id)" class="post-new-comment-send">
          <img src="../../../../../../public/web/img/icons/send-icon.svg" alt="send">
        </button>
      </div>
    </div>

  </div>
</template>

<script>
import FeedComment from "./feed-comment.vue";
import buttonWhite from "../../../parts/button.vue";
import store from "../../../store/store.js";
import api from "../../../api/api.js";
import deletePost from "../../../components/modals/delete-post.vue";

export default {
    props: {
      item: {
        donations: Number
      },
      myFeed:Boolean,
      index: String,
      peoplesDonate: 0,
    },
    computed: {
      deletePost() {
        return deletePost;
      },
  },
    components: {
        buttonWhite,
        FeedComment,
    },
    data() {
        return {
            donate: 50,
            comments: [],
            commentsTotal: 0,
            currentPage: 1,
            endReached: false,
            data: {
                title: "Blossom's Embrace",
                text: 'Discover the secret garden hidden within the game world. Unearth the rarest and most beautiful flowers, proving your commitment to the art of floral exploration. Discover the secret garden hidden within the game world. Unearth the rarest and most beautiful flowers, proving your commitment to the art of floral exploration. Discover the secret garden hidden within the game world. Unearth the rarest and most beautiful flowers, proving your commitment to the art of floral exploration.',
                image: '/web/img/achievements/Frame36300.svg',
                balance: '50',
                comments: []
            },
            moreButton: 'Read more',
            actionStatuses: {
                default: true,
                send: false,
                sendSuccess: false
            },
            newMessage: '',
        }
    },
    methods: {
        changeHeight() {
            const textarea = this.$refs.textarea;
            let height = textarea.scrollHeight;


            if (height > 63) {
                textarea.style.height = 63 + 'px';
            } else {
                textarea.style.height = '21px';
                height = textarea.scrollHeight;
                textarea.style.height = height + 'px';
            }
        },
        readMoreToggle() {
            const cardText = this.$refs.cardText;
            if (this.moreButton === 'Read more') {
                this.moreButton = 'Read less'
                cardText.style.height = cardText.scrollHeight + 'px';
                this.$refs.arrowDown.style.transform = 'rotate(180deg)';
            } else {
                this.moreButton = 'Read more'
                cardText.style.height = '63px'
                this.$refs.arrowDown.style.transform = 'initial';
            }
        },
        getAvatar() {
            let user = JSON.parse(localStorage.getItem('user'));
            if (user.avatar && user.avatar !== '/images/avatar/default-profile-img.png'){
                return user.avatar;
            } else {
                return '/web/img/user.svg';
            }
        },
        addAmbars() {
            this.actionStatuses.default = false;
            this.actionStatuses.send = true;
        },
      closeAmbarsAdd() {
        this.actionStatuses.default = true;
        this.actionStatuses.send = false;
      },
      openDeletePostModal(postId) {
        store.state.deletePostModal = {
          title: 'Delete this post?',
          btn_text: 'Delete',
          post_id: postId,
          show: true
        }
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
        increaseDecreaseAmbars(operator) {
            if (operator === '+') {
                this.data.balance++
            } else {
                if (this.data.balance > 0) {
                    this.data.balance--
                }
            }
        },
        async sendAmbars(postId, donate) {

          let authUser = JSON.parse(localStorage.getItem('user'));
          while (!authUser) {
            await new Promise(resolve => setTimeout(resolve, 250));
            authUser = JSON.parse(localStorage.getItem('user'));
          }
          await api.post('/api/feed/donate', {'id': postId, 'amount': donate}).then(response => {
            if (response && response.data) {

              this.actionStatuses.send = false;
              this.actionStatuses.sendSuccess = true;

              setTimeout(()=>{
                this.actionStatuses.sendSuccess = false;
                this.actionStatuses.default = true;
                this.item.donations = (Number(this.item.donations) + Number(donate));

              }, 3000)
            }
          }).catch(error => {
            console.error('Feed fetching data error:', error);
          });
        },
       async sendMessage(postId) {
          if (this.newMessage.length) {
            await api.post('/api/feed/comment', {'id': postId, 'comment': this.newMessage}).then(response => {
              if (response && response.data) {

                let authUser = JSON.parse(localStorage.getItem('user'));
                while (!authUser) {
                   new Promise(resolve => setTimeout(resolve, 250));
                  authUser = JSON.parse(localStorage.getItem('user'));
                }

                const currentDate = new Date();

                const months = [
                  'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'
                ];

                const year = currentDate.getFullYear();
                const month = months[currentDate.getMonth()];
                const day = currentDate.getDate();

                const newComment = {
                  id: response.data.comment,
                  body: this.newMessage,
                  created_at: `${month} ${day}, ${year}`,
                  creator: {
                    id: authUser.id,
                    username: authUser.username,
                    avatar: authUser.avatar && authUser.avatar !== '/images/avatar/default-profile-img.png' ? authUser.avatar : null
                  }
                };

                this.item.comments.unshift(newComment);

                this.newMessage = '';
                this.$refs.textarea.style.height = '21px';

                this.item.comments_count += 1;
                this.comments += 1;

                console.log(newComment, "newComment");

              }
            }).catch(error => {
              console.error('Comment fetching data error:', error);
            });
          }
        },
      getCurrentDate() {
        const currentDate = new Date();
        const months = [
          'January', 'February', 'March', 'April', 'May', 'June',
          'July', 'August', 'September', 'October', 'November', 'December'
        ];

        const year = currentDate.getFullYear();
        const month = months[currentDate.getMonth()];
        const day = currentDate.getDate();

        return `${month} ${day}, ${year}`;
      },
      navigateToVirtualHall(username) {
          window.open(`/virtual-hall/${username}`, '_blank');
      },
      async showMoreComments() {
        if (this.endReached) return;

        await api.get('/api/feed/comments/'+this.item.id+'?page='+this.currentPage).then(response => {
          if (response && response.data) {
            const newItems = response.data.data;
            store.state.comments = response.data.data;

            this.commentsTotal = response.data.meta.total;
            if (newItems.length === 0) {
              this.endReached = true;
            } else {
              this.comments = [...this.comments, ...newItems];
              this.item.comments = this.comments;
              store.state.comments = this.comments;

            }
          }
        }).catch(error => {
          console.error('Feed more comments data error:', error);
        });
      },
      checkShowMoreComments(item) {
        return item.comments_count > item.comments.length;
      },
      checkTotalCommentsText(item) {
        return item.comments_count === item.comments.length;
      },
      handleScroll() {
        const scrollContainer = this.$refs['scrollable_' + this.index];
        if (scrollContainer.scrollTop + scrollContainer.clientHeight + 1 === scrollContainer.scrollHeight
            && this.comments
            && this.comments.length < this.commentsTotal
        ) {
          console.log('Nedd to scroll');
          this.currentPage++;
          this.showMoreComments();
        }
      },
      getImageUrl(){
        if (this.item.postable_type === 'App\\Models\\Achievement'){
            return `/storage/achievements/${this.item.entity.image}`;
          }
        if (this.item.postable_type === 'App\\Models\\Badge'){
            return `/storage/integrations/${this.item.badge_integration}/${this.item.entity.image}`;
        }
        else{
            return `/storage/trophies/${this.item.entity.image}`;
          }
      },
      handleEnterMessage(event, id){
        if (event.key === "Enter" && !event.shiftKey) {
          this.sendMessage(id)
          event.preventDefault();
        }
      }
    },
}
</script>

<style lang="scss" scoped>
.post {
  padding: 24px 28px;
  background: rgba(14,15,17,0.7);
  border: 1px solid rgba(42,44,46,0.7);
  transition: border-color 0.2s;
  margin-bottom: 20px;
}
.post:hover { border-color: rgba(255,97,0,0.2); }

.post-header {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 16px;
}
.post-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; color: var(--bg); font-weight: bold;
  flex-shrink: 0; overflow: hidden;
  background: linear-gradient(135deg, var(--primary), var(--accent));
  cursor: pointer;
}
.post-avatar img { width: 100%; height: 100%; object-fit: cover; }
.post-user-info { flex: 1; min-width: 0; }
.post-username {
  font-size: 13px; color: var(--text);
  letter-spacing: 0.04em; cursor: pointer;
}
.post-username:hover { color: var(--primary); }
.post-timestamp {
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.1em;
}
.post-delete {
  margin-left: auto;
  color: var(--text-dim); cursor: pointer;
  font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase;
  padding: 4px 10px; border: 1px solid transparent;
  transition: all 0.15s; background: none;
  font-family: var(--mono);
}
.post-delete:hover { color: #e24b4a; border-color: rgba(226,75,74,0.3); }

/* Image content */
.post-image {
  margin-bottom: 14px;
  border: 1px solid rgba(42,44,46,0.6);
  overflow: hidden;
}
.post-image > img {
  width: 100%; max-height: 300px; object-fit: cover;
  display: block;
}
.post-image-caption {
  padding: 14px 16px;
  background: rgba(14,15,17,0.8);
}
.post-image-title {
  font-size: 13px; color: var(--text);
  letter-spacing: 0.03em; margin-bottom: 3px;
}
.post-image-desc {
  font-size: 11px; color: var(--text-muted);
  letter-spacing: 0.03em; line-height: 1.5;
  margin: 0;
  height: 63px; overflow: hidden;
  transition: height 0.3s ease;
}
.read-more-toggle {
  color: var(--primary); cursor: pointer;
  font-size: 11px; letter-spacing: 0.08em;
  margin-top: 6px;
  display: inline-flex; align-items: center; gap: 4px;
}
.read-more-arrow {
  width: 10px; height: 10px;
  transition: transform 0.2s;
}

/* Post actions */
.post-actions {
  display: flex; align-items: center; gap: 16px;
  padding-top: 14px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.pact {
  display: inline-flex; align-items: center; gap: 7px;
  font-size: 10px; color: var(--text-dim);
  letter-spacing: 0.12em; text-transform: uppercase;
  padding: 6px 10px; border: 1px solid transparent;
  transition: all 0.15s; cursor: pointer;
  font-family: var(--mono); background: none;
}
.pact-icon { width: 14px; height: 14px; }
.pact:hover {
  color: var(--primary);
  border-color: rgba(255,97,0,0.2);
  background: rgba(255,97,0,0.04);
}
.pact-count {
  font-family: var(--display); font-size: 16px;
  color: var(--text-muted); line-height: 1;
}
.pact:hover .pact-count { color: var(--primary); }
.pact.donate-btn:hover {
  color: var(--accent);
  border-color: rgba(193,245,39,0.2);
  background: rgba(193,245,39,0.04);
}
.pact.donate-btn:hover .pact-count { color: var(--accent); }
.pact.pact-static { cursor: default; }
.pact.pact-static:hover { color: var(--text-dim); border-color: transparent; background: none; }
.pact.pact-static:hover .pact-count { color: var(--text-muted); }

/* Donate send mode */
.donate-send {
  display: flex; align-items: center; gap: 10px;
  padding-top: 14px; border-top: 1px solid rgba(42,44,46,0.5);
  flex-wrap: wrap;
}
.donate-amount {
  display: flex; align-items: center; gap: 6px;
}
.donate-amount button {
  width: 28px; height: 28px;
  background: var(--surface-2); border: 1px solid var(--border);
  color: var(--text-muted); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--mono); font-size: 16px;
  transition: all 0.15s;
}
.donate-amount button:hover { color: var(--text); border-color: var(--text-dim); }
.donate-amount-val {
  font-family: var(--display); font-size: 22px;
  color: var(--text); min-width: 40px; text-align: center;
}
.donate-send-btn {
  padding: 8px 16px; font-size: 10px;
  letter-spacing: 0.18em; text-transform: uppercase;
  background: var(--primary); color: var(--bg);
  border: 1px solid var(--primary);
  box-shadow: 0 0 10px rgba(255,97,0,0.25);
  cursor: pointer; transition: all 0.15s;
  font-family: var(--mono);
}
.donate-send-btn:hover { background: #ff7e2e; }
.donate-cancel-btn {
  padding: 8px 12px; font-size: 10px;
  letter-spacing: 0.15em; text-transform: uppercase;
  color: var(--text-muted); border: 1px solid var(--border);
  background: transparent; cursor: pointer;
  font-family: var(--mono); transition: all 0.15s;
}
.donate-cancel-btn:hover { color: var(--text); }
.donate-status {
  font-size: 11px; color: var(--accent);
  letter-spacing: 0.1em;
}

/* Comments list */
.post-comments {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid rgba(42,44,46,0.5);
  display: flex; flex-direction: column; gap: 8px;
}
.post-comments.scroll-height {
  max-height: 260px;
  overflow-y: auto;
}
.post-show-more {
  font-size: 10px; color: var(--text-muted);
  letter-spacing: 0.14em; text-transform: uppercase;
  padding: 6px 0; margin-top: 6px;
  background: none; border: none; cursor: pointer;
  font-family: var(--mono);
  text-align: left;
}
.post-show-more:hover { color: var(--primary); }

/* New comment */
.post-new-comment {
  display: flex; align-items: flex-start; gap: 10px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(42,44,46,0.5);
}
.post-new-comment-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  object-fit: cover; flex-shrink: 0;
  background: var(--surface-2);
}
.post-new-comment-input {
  flex: 1; min-width: 0;
  display: flex; align-items: flex-start; gap: 8px;
  background: var(--surface-2);
  border: 1px solid var(--border);
  padding: 8px 10px;
}
.post-new-comment-input:focus-within { border-color: var(--primary); }
.post-new-comment-input textarea {
  flex: 1; min-width: 0;
  background: transparent; border: none; outline: none;
  color: var(--text); font-family: var(--mono);
  font-size: 12px; letter-spacing: 0.02em;
  resize: none;
  height: 21px; line-height: 1.5;
  max-height: 63px;
}
.post-new-comment-input textarea::placeholder { color: var(--text-dim); }
.post-new-comment-send {
  background: none; border: none;
  padding: 0; cursor: pointer;
  flex-shrink: 0; opacity: 0.7;
  transition: opacity 0.15s;
}
.post-new-comment-send:hover { opacity: 1; }
.post-new-comment-send img { width: 18px; height: 18px; }

@media (max-width: 700px) {
  .post { padding: 18px 20px; }
}
</style>
