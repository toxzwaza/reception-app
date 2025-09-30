<template>
  <div
    :class="[
      'bg-white rounded-xl shadow-sm overflow-hidden transition-shadow duration-200',
      hoverable && 'hover:shadow-lg',
      padding && 'p-6'
    ]"
  >
    <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gray-200">
      <div v-if="title" class="text-lg font-medium text-gray-900">
        {{ title }}
      </div>
      <slot name="header"></slot>
    </div>
    <div :class="[padding ? '' : 'p-6']">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" :class="['px-6 py-4 border-t border-gray-200', footerClass]">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  hoverable: {
    type: Boolean,
    default: false
  },
  padding: {
    type: Boolean,
    default: false
  },
  footerClass: {
    type: String,
    default: ''
  }
});
</script>
