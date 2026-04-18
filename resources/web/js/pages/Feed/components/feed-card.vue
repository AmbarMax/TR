<template>
  <div class="fc" :id="item.id+'feed-dom-obj'">

    <!-- Header -->
    <div class="fc-header">
      <div class="fc-user" @click="navigateToVirtualHall(item.creator.username)">
        <div class="fc-avatar">
          <img v-if="!item.creator.avatar" src="../../../../images/web/img/user.svg" alt="post-user" class="fc-avatar__img">
          <img v-else :src="item.creator.avatar" alt="user" class="fc-avatar__img">
        </div>
        <span class="fc-username">{{ item.creator.username }}</span>
      </div>
      <div class="fc-header__right">
        <span class="fc-date">{{ item.created_at }}</span>
        <button class="fc-delete" v-if="myFeed || this.isModerator() === true" @click="openDeletePostModal(item.id)">
          <img src="../../../../images/web/img/icons/trash.svg" alt="trash-icon">
        </button>
      </div>
    </div>

    <!-- Title + Description -->
    <h2 class="fc-title">{{ item.entity.name }}</h2>
    <p class="fc-text" ref="cardText">{{ item.entity.description }}</p>
    <div class="fc-read-more" v-if="item.entity.description && item.entity.description.length > 150" @click="readMoreToggle">
      <span>{{ moreButton }}</span>
      <img src="../../../../images/web/img/icons/arrow-down.svg" alt="arrow" ref="arrowDown" class="fc-read-more__arrow">
    </div>

    <!-- Image -->
    <div class="fc-image">
      <img :src="getImageUrl()" alt="post-image">
    </div>

    <!-- Actions -->
    <div class="fc-actions">
      <!-- Default state -->
      <div class="fc-balance-row" v-if="actionStatuses.default">
        <div class="fc-balance">
          <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="fc-balance__icon">
          <span class="fc-balance__value">{{ item.donations }}</span>
        </div>
        <button class="fc-donate-btn" v-if="!myFeed" @click="addAmbars">Donate</button>
        <button class="fc-donate-btn fc-donate-btn--disabled" disabled v-else>{{ item.donations_count }} people donated</button>
      </div>

      <!-- Send state -->
      <div class="fc-balance-row" v-if="actionStatuses.send">
        <div class="fc-count">
          <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="fc-balance__icon">
          <button class="fc-operator" @click="increaseDecreaseAmbars('-')">
            <img src="../../../../../../public/web/img/icons/green-minus.svg" alt="minus">
          </button>
          <span class="fc-count__value">{{ data.balance }}</span>
          <button class="fc-operator" @click="increaseDecreaseAmbars('+')">
            <img src="../../../../../../public/web/img/icons/green-plus.svg" alt="plus">
          </button>
        </div>
        <button-white :text="'Send'" @click="sendAmbars(item.id, data.balance)"></button-white>
      </div>

      <!-- Success state -->
      <div class="fc-balance-row" v-if="actionStatuses.sendSuccess">
        <div class="fc-count">
          <img src="../../../../images/web/img/points/ambar.svg" alt="ambars" class="fc-balance__icon">
          <span class="fc-count__value">{{ data.balance }}</span>
        </div>
        <span class="fc-status">
          {{ item.donations_count === 1 || item.donations_count === 0
            ? 'You donated Ambars'
            : `You and ${item.donations_count} people donated Ambars` }}
        </span>
      </div>
    </div>

    <!-- Comments -->
    <div class="fc-comments" :ref="'scrollable_'+index" :class="{'scroll-height': item.comments_count > 3}" @scroll="handleScroll">
      <feed-comment v-for="comment of item.comments" :comment="comment" :post="item.id"></feed-comment>
    </div>
    <button v-if="item.comments_count > 3" class="fc-show-more">
      <span v-if="checkShowMoreComments(item)" @click="showMoreComments(item.id)">Show more comments ({{ item.comments_count }})</span>
      <span v-if="checkTotalCommentsText(item)">Total ({{ item.comments_count }})</span>
    </button>

    <!-- New comment -->
    <div class="fc-new-comment">
      <img :src="getAvatar()" alt="user-avatar" class="fc-new-comment__avatar">
      <div class="fc-new-comment__input">
        <textarea
          ref="textarea"
          @input="changeHeight"
          v-model="newMessage"
          placeholder="Leave comment..."
          @keydown="event => handleEnterMessage(event, item.id)"
        ></textarea>
        <button @click="sendMessage(item.id)" class="fc-new-comment__send">
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

<style scoped>
.fc {
  background: #0e0f11;
  border: 1px solid #2a2c2e;
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 16px;
}

/* Header */
.fc-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.fc-user {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.fc-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fc-avatar__img {
  width: 36px;
  height: 36px;
  object-fit: cover;
  border-radius: 50%;
}

.fc-username {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.fc-header__right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.fc-date {
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.fc-delete {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  opacity: 0.4;
  transition: opacity 0.15s;
}

.fc-delete:hover {
  opacity: 1;
}

/* Title + text */
.fc-title {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 15px;
  font-weight: 400;
  margin: 0 0 8px;
}

.fc-text {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  line-height: 1.5;
  margin: 0 0 4px;
  height: 63px;
  overflow: hidden;
  transition: height 0.2s;
}

.fc-read-more {
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  margin-bottom: 12px;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px;
}

.fc-read-more__arrow {
  width: 12px;
  height: 12px;
  transition: transform 0.2s;
}

/* Image */
.fc-image {
  margin-top: 12px;
  margin-bottom: 12px;
  border-radius: 4px;
  overflow: hidden;
}

.fc-image img {
  width: 100%;
  max-height: 400px;
  object-fit: cover;
  border-radius: 4px;
  display: block;
}

/* Actions */
.fc-actions {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1px solid #1a1c1f;
}

.fc-balance-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.fc-balance {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
}

.fc-balance__icon {
  width: 18px;
  height: 18px;
}

.fc-balance__value {
  color: #9a9590;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}

.fc-donate-btn {
  background: transparent;
  border: 1px solid #ff6100;
  color: #ff6100;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  padding: 6px 14px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.15s;
}

.fc-donate-btn:hover {
  background: rgba(255, 97, 0, 0.1);
}

.fc-donate-btn--disabled {
  border-color: #2a2c2e;
  color: #5a5550;
  cursor: default;
}

.fc-donate-btn--disabled:hover {
  background: transparent;
}

.fc-count {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.fc-count__value {
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  min-width: 24px;
  text-align: center;
}

.fc-operator {
  background: transparent;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 0;
}

.fc-operator img {
  width: 18px;
  height: 18px;
}

.fc-status {
  color: #c1f527;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
}

/* Comments */
.fc-comments {
  overflow-y: auto;
  margin-top: 12px;
  border-top: 1px solid #1a1c1f;
}

.scroll-height {
  height: 350px;
  overflow-y: auto;
}

.scroll-height::-webkit-scrollbar {
  width: 5px;
}

.scroll-height::-webkit-scrollbar-thumb {
  background-color: #ff6100;
  border-radius: 4px;
}

.fc-show-more {
  background: transparent;
  border: none;
  cursor: pointer;
  width: 100%;
  text-align: center;
  padding: 8px 0;
  color: #5a5550;
  font-family: 'Share Tech Mono', monospace;
  font-size: 12px;
  transition: color 0.15s;
}

.fc-show-more:hover {
  color: #9a9590;
}

/* New comment */
.fc-new-comment {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #1a1c1f;
}

.fc-new-comment__avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  border: 1px solid #2a2c2e;
}

.fc-new-comment__input {
  flex: 1;
  background: #1a1c1f;
  border: 1px solid #2a2c2e;
  border-radius: 4px;
  display: flex;
  align-items: flex-end;
  padding: 6px 10px;
  gap: 8px;
}

.fc-new-comment__input textarea {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
  resize: none;
  height: 21px;
  line-height: 1.5;
}

.fc-new-comment__input textarea::placeholder {
  color: #5a5550;
}

.fc-new-comment__send {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.fc-new-comment__send img {
  width: 18px;
  height: 18px;
  filter: invert(40%) sepia(80%) saturate(600%) hue-rotate(350deg) brightness(100%);
}

@media (max-width: 968px) {
  .fc-balance-row {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
