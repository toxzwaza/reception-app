<template>
  <div class="space-y-8">
    <!-- セクションヘッダー -->
    <div v-if="title || description" class="border-b border-gray-200 pb-5">
      <h3 v-if="title" class="text-lg font-medium leading-6 text-gray-900">{{ title }}</h3>
      <p v-if="description" class="mt-2 text-sm text-gray-600">{{ description }}</p>
    </div>

    <!-- フォームフィールド -->
    <div class="space-y-6">
      <slot></slot>
    </div>

    <!-- フォームアクション -->
    <div v-if="$slots.actions" class="flex justify-end space-x-3 pt-5 border-t border-gray-200">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  }
});
</script>
