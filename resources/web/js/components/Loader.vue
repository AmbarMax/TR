<template>
    <div class="loader">
        <div class="loader_icon">
            <img src="../../../web/images/web/img/icons/loader.svg">
        </div>
        <div class="loader_text">
            Please wait...
        </div>
    </div>
</template>

<script>
export default {
    name: 'Loader',
}
</script>

<style scoped>
.loader {
    display: flex;
    flex-direction: column;
    margin: 200px auto;
    width: 168px;
    height: 140px;
}

.loader_icon {
    margin: auto;
}

.loader_text {
    color: rgba(255, 255, 255, 0.90);
    font-size: 20px;
    font-family: JetBrains Mono;
    font-weight: 400;
    line-height: 28px;
    word-wrap: break-word;
}

.loader_icon img {
    transform-origin: center;
    animation: rotate 1s steps(8) infinite;
}

@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
