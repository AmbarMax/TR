<template>
    <div class="pagination">
        <button class="arrow_left" @click="prevPage" :disabled="currentPage === 1"> &#60; </button>
        <span v-for="pageNumber in filteredPages" :key="pageNumber">
      <button @click="goToPage(pageNumber)" :class="{ active: pageNumber === currentPage }">{{ pageNumber }}</button>
    </span>
        <button class="arrow_right" @click="nextPage" :disabled="currentPage === totalPages"> &#62; </button>
    </div>
</template>

<script>
export default {
    props: {
        currentPage: Number,
        totalPages: Number,
    },
    methods: {
        prevPage() {
            if (this.currentPage > 1) {
                this.$emit('page-changed', this.currentPage - 1);
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.$emit('page-changed', this.currentPage + 1);
            }
        },
        goToPage(pageNumber) {
            if (pageNumber !== '..') {
                this.$emit('page-changed', pageNumber);
            }
        },
    },
    computed: {
        filteredPages() {
            const firstTwoPages = [1, 2];
            const lastTwoPages = [this.totalPages - 1, this.totalPages];

            if (this.totalPages <= 2) {
                return [...Array(this.totalPages).keys()].map((i) => i + 1);
            } else if (this.currentPage <= 2) {
                return [...firstTwoPages, '..', ...lastTwoPages];
            } else if (this.currentPage >= this.totalPages - 1) {
                return [...firstTwoPages, '..', ...lastTwoPages];
            } else {
                return [...firstTwoPages, '..', this.currentPage, '..', ...lastTwoPages];
            }
        },
    },
};
</script>


<style scoped>
.pagination {
    margin: auto;
    text-align: center;
    margin-top: 20px;
}

button {
    width: 36px;
    height: 36px;
    color: white;
    background: unset;
    padding: 5px 10px;
    border: 1px #BABABA solid;
    color: #BABABA;
    font-size: 16px;
    font-family: 'Share Tech Mono', monospace;
    font-weight: 400;
    line-height: 20.48px;
    word-wrap: break-word
}

button.active {
    background-color: #CAFB01;
    color: #18181B;
    border: none;
}
</style>
