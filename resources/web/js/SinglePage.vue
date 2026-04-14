<template>
<!--    <sideBar></sideBar>-->
    <div class="page">
<!--        <UnauthorizedHeader></UnauthorizedHeader>-->
        <MainHeader class="single-page-header"></MainHeader>
        <router-view></router-view>
    </div>
</template>

<script>

// import UnauthorizedHeader from "./components/unauthorized-header.vue";
import MainHeader from "./components/main-header.vue";
import sideBar from "./components/sidebar.vue";

export default {
    components: {
        sideBar,
        // UnauthorizedHeader,
        MainHeader
    },
}
</script>

<style>

</style>
