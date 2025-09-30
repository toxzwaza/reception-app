<template>
  <div class="text-center">
    <!-- 完了アイコン -->
    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100 mb-8">
      <svg class="h-16 w-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 48 48">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M20 36l-8-8 4-4 4 4 12-12 4 4-16 16z"
        />
      </svg>
    </div>

    <!-- タイトルと説明 -->
    <h2 class="text-2xl font-bold text-gray-900 mb-3">
      {{ title }}
    </h2>
    <div class="text-gray-600 mb-8">
      <slot name="description"></slot>
    </div>

    <!-- メインコンテンツ -->
    <div class="mb-8">
      <slot></slot>
    </div>

    <!-- アクションボタン -->
    <div class="flex justify-center space-x-4">
      <slot name="actions">
        <Button
          variant="primary"
          :href="route('home')"
          class="min-w-[120px]"
        >
          トップに戻る
        </Button>
      </slot>
    </div>
  </div>
</template>

<script setup>
import Button from '@/Components/UI/Button.vue';

defineProps({
  title: {
    type: String,
    required: true
  }
});
</script>
