<template>
    <div class="list-wrapper-user-list mt-30 message-notification__list" ref="scrollable" @scroll="handleScroll" v-if="items">
        <div @click="SelectUser(item.id)" class="item notification-message" v-for="item in items.slice(0, this.currentPage)" :key="item.id" :class="{ 'selected-user': selectUser === item.id }">
            <div class="left-part">
                <img v-if="!item.avatar" src="../../images/web/img/user.svg" alt="user">
                <img v-else :src="'storage/users/' + item.id + '/avatar/' + item.avatar +'.jpg'" alt="user">
                <span>
                   <span class="email-title">{{ item.email }}</span>
                   <span class="name-title">{{ item.name }}</span>
                </span>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    data() {
        return {
            selectUser: null,
            currentPage: 10,
        }
    },
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
    methods: {
        SelectUser(id){
            this.selectUser = id;
            this.$emit('childClick', id);
        },
        handleScroll() {
            const scrollContainer = this.$refs.scrollable;
            if (scrollContainer.scrollTop + scrollContainer.clientHeight === scrollContainer.scrollHeight && this.items) {
                this.currentPage = this.currentPage + 10;
            }
        },
    },
};
</script>

<style scoped>

.list-wrapper-user-list {
    background: #303135;
    width: 100%;
    max-height: 300px;
    overflow: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.item {
    background: rgba(186, 186, 186, 0.15);
}


.selected-user {
    border: 1px solid #CAFB01!important;
}

.item {
    display: flex;
    justify-content: space-between;
    padding: 18px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.left-part {
    display: flex;
    align-items: center;
}

.left-part img {
    margin-right: 10px;
    cursor: pointer;
}

.left-part span {
    display: block;
}

img {
    width: 50px;
    height: 50px;
    border-radius: 8px;
}

.email-title,
.name-title{
    font-family: 'Share Tech Mono', monospace;
}

.email-title,
.name-title {
    color: white;
    cursor: pointer;
}

.name-title {
    color: #BABABA;
    padding-top: 10px;
    font-size: 16px;
}

@media (max-width: 1065px) {
    .item{
        flex-direction: column;
    }
}

@media (max-width: 371px) {
    .email-title,
    .name-title
    {
        word-break: break-word;
    }
}

.list-wrapper-user-list:hover {
    cursor: pointer;
}

</style>
