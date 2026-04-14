<template>
    <div class="page-pagination-wrapper" v-if="items.length && totalPages > 1">

      <div>
        <button class="mobile_arrow_left" @click="goToPrevPage"> &#60; </button>
      </div>

      <div class="green-bordered-block-left" @click="goToPrevPage">
        <span class="arrow-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 92 92" id="left-arrow" style="display: block; margin: auto;">
            <path d="M84 46c0 2.2-1.8 4-4 4H21.6l18.1 18.2c1.6 1.6 1.6 4.1 0 5.7-.7.7-1.7 1.1-2.8 1.1-1 0-2.1-.4-2.8-1.2l-24.9-25c-1.6-1.6-1.6-4.1 0-5.6l24.9-25c1.6-1.6 4.1-1.6 5.7 0 1.6 1.6 1.6 4.1 0 5.7L21.6 42H80c2.2 0 4 1.8 4 4" fill="#CAFB01"></path>
          </svg>
        </span>
        <span class="arrow-label">
          Previous
        </span>
      </div>

      <div class="pages">

        <span @click="1 !== insideCurrentPage && goToPage(1)"
            :class="{'current-page page': insideCurrentPage === 1,'page': insideCurrentPage !== 1 }">
          1
        </span>

        <span class="dots" v-if="insideCurrentPage > fixedCount && totalPages > 6">...</span>

        <span
          v-for="page in visiblePages"
          :key="page"
          class="page"
          :class="{ 'current-page': page === insideCurrentPage }"
          @click="goToPage(page)"
      >
        {{ page }}
      </span>

        <span class="dots" v-if="totalPages > fixedCount +1 && insideCurrentPage !== totalPages && insideCurrentPage !== totalPages -1">...</span>

        <span @click="insideCurrentPage !== totalPages && goToPage(totalPages)"
              :class="{'current-page page': insideCurrentPage === totalPages,'page': insideCurrentPage !== totalPages }"
        >
          {{totalPages}}
        </span>

      </div>

      <div class="green-bordered-block-right" @click="goToNextPage">
        <span class="arrow-label">Next</span>
        <span class="arrow-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 92 92" id="right-arrow" style="display: block; margin: auto;">
            <path d="M8 46c0 2.2 1.8 4 4 4h58.4l-18.1 18.2c-1.6 1.6-1.6 4.1 0 5.7.7.7 1.7 1.1 2.8 1.1 1 0 2.1-.4 2.8-1.2l24.9-25c1.6-1.6 1.6-4.1 0-5.6l-24.9-25c-1.6-1.6-4.1-1.6-5.7 0-1.6 1.6-1.6 4.1 0 5.7L70.4 42H12c-2.2 0-4 1.8-4 4" fill="#CAFB01"></path>
          </svg>
        </span>
      </div>

      <div>
        <button class="mobile_arrow_right" @click="goToNextPage"> &#62; </button>
      </div>

    </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array,
      required: true,
    },
    total: {
      type: Number,
    },
    currentPage: {
      type: Number
    },
    itemsPerPage: {
      type: Number
    },
    method: {
      type: Function
    }
  },
  data() {
    return {
      insideCurrentPage: 1,
      fixedCount: 4,
      paginateStart: 1,
      preloader: false
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.total / this.itemsPerPage);
      // return 30;
    },
    pages() {
      return Array.from({ length: this.totalPages }, (_, index) => index + 1);
    },
    visiblePages() {

      const pages = [];

      for (let i = this.paginateStart; i < this.totalPages; i++) {
        if (i !== 1 && i !== this.totalPages) {
          pages.push(i);
        }
        if (pages.length === this.fixedCount) {
          break;
        }
      }

        if (this.totalPages > 6){
          return pages;
        }else{
          if(this.totalPages === 2){
            return [];
          }
          if (this.totalPages === 3){
            return [2];
          }
          if (this.totalPages === 4){
            return [2, 3];
          }
          if (this.totalPages === 5){
            return [2, 3, 4];
          }
          if (this.totalPages === 6){
            return [2, 3, 4, 5];
          }
        }
    },
  },
  methods: {
    goToPage(page) {

      this.scrollToTop()

      this.method(page);
      this.insideCurrentPage = page;

      const lastElement = this.visiblePages[this.visiblePages.length - 1];
      const lastStart = this.totalPages - this.fixedCount;

      const lastCollection = Array.from({ length: this.fixedCount }, (_, index) => this.totalPages - index - 1);
      lastCollection.push(this.totalPages);

      const firstCollection = Array.from({ length: this.fixedCount }, (_, index) => index + 1);
      const isNumberIncludedFirst = firstCollection.includes(this.insideCurrentPage);

      if (page === 1) {
        this.paginateStart = 1;
      }

      const isNumberIncludedLast = lastCollection.includes(this.insideCurrentPage);

      if (lastElement === this.insideCurrentPage && !isNumberIncludedLast) {
          this.paginateStart = lastElement;
      }
      if (isNumberIncludedLast) {
        this.paginateStart = lastStart;
      }

      if (!isNumberIncludedLast && lastElement !== this.insideCurrentPage && isNumberIncludedFirst) {
        this.paginateStart = 1;
      }

      if (!isNumberIncludedFirst && !isNumberIncludedLast) {
        this.paginateStart = this.insideCurrentPage;
      }
    },
    goToPrevPage() {
      if (this.insideCurrentPage !== 1) {
        this.insideCurrentPage--;
        this.goToPage(this.insideCurrentPage);
      }
    },
    goToNextPage() {
      if (this.totalPages !== this.insideCurrentPage && this.totalPages > 1) {
        this.insideCurrentPage++;
        this.goToPage(this.insideCurrentPage);
      }
    },
    scrollToTop() {
      const scrollToTop = () => {
        const c = document.documentElement.scrollTop || document.body.scrollTop;

        if (c > 0) {
          window.requestAnimationFrame(scrollToTop);
          window.scrollTo(0, c - c / 8);
        }
      };

      scrollToTop();
    }
  },
}
</script>

<style scoped>
.page-pagination-wrapper{
  display: flex;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  margin-top: 42px;
  padding-top: 20px;
  margin-bottom: 50px;
}

.arrow-icon{
  color: #CAFB01;
  width: 22px;
  height: 22px;
}

.green-bordered-block-left, .green-bordered-block-right{
  border-radius: 2px;
  border: 1px #CAFB01 solid;
  white-space: nowrap;
  text-align: center;
  color: #CAFB01;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
}
.green-bordered-block-left .arrow-icon{
  padding-left: 22px;
  padding-top: 7px;
  padding-right: 8px;
}
.green-bordered-block-left .arrow-label{
  padding: 10px 22px;
}
.green-bordered-block-right{
  padding-right: 22px;
}
.green-bordered-block-right .arrow-icon{
  padding-left: 8px;
  padding-top: 7px;
  padding-right: 22px;
}
.green-bordered-block-right .arrow-label{
  padding-left: 22px;
  padding-top: 10px;
}

.pages{
  display: flex;
}

.current-page{
  color: black!important;
  background: #CAFB01;
}

.page, .dots{
  font-family: JetBrains Mono;
  color: #BABABA;
  border-radius: 2px;
  padding: 10px 18px;
  justify-content: center;
}

.page{
  cursor: pointer;
}

.mobile_arrow_left, .mobile_arrow_right{
  width: 36px;
  height: 36px;
  background: unset;
  padding: 5px 10px;
  border: 1px #BABABA solid;
  color: #BABABA;
  font-size: 16px;
  font-family: JetBrains Mono;
  font-weight: 400;
  line-height: 20.48px;
  word-wrap: break-word;
  display: none;
}

@media (max-width: 1145px) {
  .page-pagination-wrapper{
    justify-content: space-around;
  }
  .green-bordered-block-left, .green-bordered-block-right{
    display: none;
  }
  .mobile_arrow_left, .mobile_arrow_right{
    display: flex;
  }
}

@media (max-width: 849px) {
  .page-pagination-wrapper{
    justify-content: space-between;
  }
}

@media (max-width: 512px) {
  .page-pagination-wrapper{
    justify-content: space-between;
    margin-left: 0;
  }
  .page, .dots{
    padding: 10px 8px;
  }
  .dots{
    padding: 10px 0;
  }
}

</style>
