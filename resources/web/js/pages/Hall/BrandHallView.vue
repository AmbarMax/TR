<template>
  <div class="brand-hall-shell">
    <h1>BrandHallView (shell)</h1>
    <p>Implementación visual real en Step 21.</p>
    <pre>{{ user }}</pre>
  </div>
</template>

<script>
export default {
  name: "BrandHallView",
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style scoped>
.brand-hall-shell {
  padding: 24px;
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  color: #ff6100;
}
.brand-hall-shell h1 {
  font-size: 18px;
  margin-bottom: 8px;
}
.brand-hall-shell p {
  color: #888;
  margin-bottom: 16px;
  font-family: system-ui, sans-serif;
}
.brand-hall-shell pre {
  background: #0a0a0e;
  padding: 16px;
  border: 1px solid #222;
  border-radius: 6px;
  overflow-x: auto;
  font-size: 12px;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
