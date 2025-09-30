<template>
  <div class="min-h-screen bg-gray-50">
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div v-if="title || $slots.header" class="mb-8">
          <h1 v-if="title" class="text-3xl font-bold text-gray-900 text-center">
            {{ title }}
          </h1>
          <slot name="header"></slot>
        </div>
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  }
});
</script>
