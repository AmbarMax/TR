<template>
    <div class="feed-card" :id="item.id+'feed-dom-obj'">
        <div class="feed-card__header">
            <div class="feed-card__user" @click="navigateToVirtualHall(item.creator.username)">
              <img v-if="!item.creator.avatar" src="../../../../images/web/img/user.svg" alt="post-user">
              <img v-else :src="item.creator.avatar" alt="user">
                <span>
                    {{ item.creator.username }}
                </span>
            </div>
            <div class="feed-card__date">
                {{ item.created_at }}
            </div>
        </div>
        <h2 class="feed-card__title">
            {{ item.entity.name }}
        </h2>
        <p class="feed-card__text" ref="cardText">
            {{ item.entity.description }}
        </p>
        <div class="feed-card__more-button" v-if="item.entity.description && item.entity.description.length > 150" @click="readMoreToggle">
            <span>
                {{ moreButton }}
            </span>
            <img src="../../../../images/web/img/icons/arrow-down.svg" alt="arrow" ref="arrowDown">
        </div>
        <div class="feed-card__images">
            <img :src="getImageUrl()" alt="main-image">
        </div>
        <div class="feed-card__actions" style="margin-top: 20px">
            <div class="feed-card__balance-row" v-if="actionStatuses.default">
                <div class="feed-card__balance">
                    <img src="../../../../images/web/img/points/ambar.svg" alt="ambars">
                    <span>
                        {{ item.donations }}
                    </span>
                </div>
                <button class="feed-card__add" v-if="!myFeed" @click="addAmbars">
                    Add Ambars
                </button>
                <button class="feed-card__add" disabled v-else>
                    {{ item.donations_count }} people added Ambars
                </button>
                <button class="feed-card__delete" v-if="myFeed || this.isModerator() === true" @click="openDeletePostModal(item.id)">
                    <img src="../../../../images/web/img/icons/trash.svg" alt="trash-icon">
                </button>
            </div>
            <div class="feed-card__balance-row" v-if="actionStatuses.send">
                <div class="feed-card__count">
                    <img src="../../../../images/web/img/points/ambar.svg" alt="ambars">
                    <button class="feed-card__operator" @click="increaseDecreaseAmbars('-')">
                        <img src="../../../../../../public/web/img/icons/green-minus.svg" alt="ambars">
                    </button>
                    <span class="feed-card__count-value">
                        {{ data.balance }}
                    </span>
                    <button class="feed-card__operator" @click="increaseDecreaseAmbars('+')">
                      <img src="../../../../../../public/web/img/icons/green-plus.svg" alt="green-plus">
                    </button>
                </div>
                <button-white :text="'Send'" @click="sendAmbars(item.id, data.balance)"></button-white>
            </div>
            <div class="feed-card__balance-row" v-if="actionStatuses.sendSuccess">
                <div class="feed-card__count">
                    <img src="../../../../images/web/img/points/ambar.svg" alt="ambars">
                    <span class="feed-card__count-value">
                        {{ data.balance }}
                    </span>
                </div>
              <div class="feed-card__status" style="margin-top: 20px">
                {{ item.donations_count === 1 || item.donations_count === 0
                  ? 'You donated Ambars'
                  : `You and ${item.donations_count} people donated Ambars` }}
              </div>
            </div>
        </div>
        <div class="feed-card__comments" :ref="'scrollable_'+index" :class="{'scroll-height': item.comments_count > 3}" @scroll="handleScroll">
            <feed-comment v-for="comment of item.comments" :comment="comment" :post="item.id"></feed-comment>
        </div>
        <button
            v-if="item.comments_count > 3"
            class="feed-card__show-more-comments"
            >
          <span v-if="checkShowMoreComments(item)" @click="showMoreComments(item.id)">Show more comments ({{item.comments_count}})</span>
          <span v-if="checkTotalCommentsText(item)" >Total ({{item.comments_count}})</span>
        </button>
        <div class="feed-card__new-comment">
            <img :src="getAvatar()" alt="user-avatar">
            <div class="feed-card__textarea">
                <textarea style="font-size: 16px"
                    ref="textarea"
                    @input="changeHeight"
                    v-model="newMessage"
                    placeholder="Leave comment..."
                    @keydown="event => handleEnterMessage(event, item.id)"
                >
                </textarea>
                <button @click="sendMessage(item.id)">
                    <img src="../../../../../../public/web/img/icons/send-icon.svg" alt="send-button">
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
@media (max-width: 968px) {
    .feed-card__count {
        margin-top: 16px;
    }
}

.scroll-height {
  overflow-y: auto;
  height: 350px;
}
.feed-card__comments{
  overflow-y: auto;
}
.scroll-height::-webkit-scrollbar {
  width: 5px;
}
.scroll-height::-webkit-scrollbar-thumb {
  background-color: #CAFB01;
  border-radius: 4px;
}
.feed-card__images{
  margin-top: 12px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  width: 560px;
  //height: 500px;
  overflow: hidden;
    object-fit: contain;
}
.feed-card__images img
{
  width: 100%;
  height: 100%;
}
@media (max-width: 642px) {
  .feed-card__images{
    //max-height: 290px;
    height: auto;
    width: auto;

  }
  .feed-card {
    width: 90vw !important;
      margin-left: 0!important;
      margin-right: 0!important;
  }

  .feed-card__header {
      flex-direction: column;
      gap: 0;
      align-items: flex-start;
      margin-bottom: 20px;
  }

    .feed-card__user img {
        margin-bottom: -20px;
    }

    .feed-card__date {
      margin-left: 60px
  }
}
@media (max-width: 552px) {
  .feed-card{
    margin-left: 0!important;
    margin-right: 0!important;
    overflow-x: hidden;
  }
  .feed-card__images img
  {
    max-width: 280px;
    width: 280px;
  }
}
</style>
