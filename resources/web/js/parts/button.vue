<template>
    <button class="main-button" :disabled="disabled">
        <img v-if="img_link === 'github'"  src="../../images/web/img/social_icons/mdi_github.svg" alt="btn_logo">
        <img v-if="img_link === 'discord'"  src="../../images/web/img/social_icons/ic_baseline-discord_black.svg" alt="btn_logo">
        <img v-if="img_link === 'steam'"  src="../../images/web/img/social_icons/mdi_steam.svg" alt="btn_logo">
        <img v-if="img_link === 'proof'"  src="../../images/web/img/social_icons/proof.svg" alt="btn_logo">
        <span>{{text}}</span>
    </button>
</template>

<script>
export default {
    props: [ 'img_link', 'text', 'disabled' ],
    data() {
        return {}
    },
}
</script>
