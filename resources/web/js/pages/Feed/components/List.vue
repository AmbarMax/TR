<template>
  <div class="list-wrapper mt-30">

    <div class="item">
      <div class="left-part">
        <img src="../../../../images/web/img/points/ambar.svg" alt="ambar">
        <span>
          <span>@wilsonh12</span>
          <span>Jenny wilson</span>
        </span>
      </div>

      <div>
        View Virtual hall
        <svg xmlns="http://www.w3.org/2000/svg" width="32" viewBox="0 0 32 32" id="delete">
          <path d="M24.2,12.193,23.8,24.3a3.988,3.988,0,0,1-4,3.857H12.2a3.988,3.988,0,0,1-4-3.853L7.8,12.193a1,1,0,0,1,2-.066l.4,12.11a2,2,0,0,0,2,1.923h7.6a2,2,0,0,0,2-1.927l.4-12.106a1,1,0,0,1,2,.066Zm1.323-4.029a1,1,0,0,1-1,1H7.478a1,1,0,0,1,0-2h3.1a1.276,1.276,0,0,0,1.273-1.148,2.991,2.991,0,0,1,2.984-2.694h2.33a2.991,2.991,0,0,1,2.984,2.694,1.276,1.276,0,0,0,1.273,1.148h3.1A1,1,0,0,1,25.522,8.164Zm-11.936-1h4.828a3.3,3.3,0,0,1-.255-.944,1,1,0,0,0-.994-.9h-2.33a1,1,0,0,0-.994.9A3.3,3.3,0,0,1,13.586,7.164Zm1.007,15.151V13.8a1,1,0,0,0-2,0v8.519a1,1,0,0,0,2,0Zm4.814,0V13.8a1,1,0,0,0-2,0v8.519a1,1,0,0,0,2,0Z">
          </path>
        </svg>
      </div>

    </div>

  </div>
</template>

<script>
export default {
  name: "List"
}
</script>

<style scoped>
.list-wrapper {
  background: #0e0f11;
  width: 100%;
}
.item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #2a2c2e;
  padding: 14px 18px;
  color: #feeddf;
  font-family: 'Share Tech Mono', monospace;
  font-size: 13px;
}
.left-part {
  display: flex;
  align-items: center;
  gap: 10px;
}
.left-part span {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.left-part span span:first-child {
  color: #feeddf;
  font-size: 13px;
}
.left-part span span:last-child {
  color: #9a9590;
  font-size: 11px;
}
</style>