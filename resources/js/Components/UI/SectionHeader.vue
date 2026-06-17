<template>
  <div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-2">
      <span v-if="$slots.icon" class="text-blue-600">
        <slot name="icon"></slot>
      </span>
      <h3 class="text-lg font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        {{ title }}
      </h3>
      <span v-if="subtitle" class="text-sm text-slate-400 font-normal">{{ subtitle }}</span>
    </div>
    <div v-if="$slots.action">
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
});
</script>
