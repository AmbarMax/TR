<template>
  <div class="subscription_prices_wrapper">
    <span class="elem">
      <span class="title_part">By following you will receive</span>
      <span class="price_part">
        <span class="sub_price">+ 1</span>
        <img src="../../../../images/web/img/points/uru.svg" alt="uru">
      </span>
    </span>
  </div>
</template>

<script>
export default {
  name: "SubscriptionPricesBlock"
}
</script>

<style scoped>
.subscription_prices_wrapper {
  display: flex;
  flex-direction: column;
  font-family: JetBrains Mono;
  border-radius: 2px;
  color: white;
  font-weight: 400;
  line-height: 2em;
  width: 100%;
  background: rgba(186, 186, 186, 0.15);
  padding: 10px 12px;
}

.elem {
  display: flex;
  justify-content: space-between;
}

.title_part {
  font-size: 16px;
}

.sub_price {
  font-size: 17px;
  font-family: Orbitron;
  font-weight: 600;
}

img {
  padding-left: 5px;
  width: 30px;
  height: auto;
  padding-top: 2px;
}

@media (min-width: 564px) {
  .subscription_prices_wrapper {
    width: 518px;
  }
}
@media (min-width: 968px) {
    .subscription_prices_wrapper {
        width: 390px;
    }
}
@media (min-width: 1600px) {
    .subscription_prices_wrapper {
        width: 518px;
    }
}
@media (max-width: 564px) {
  .title_part {
    word-break: break-all;
  }
  .elem {
    flex-direction: column;
  }
}
</style>
