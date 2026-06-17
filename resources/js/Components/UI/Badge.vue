<template>
  <span :class="classes">
    <span v-if="dot" :class="['w-1.5 h-1.5 rounded-full mr-1.5', dotColor]"></span>
    <slot></slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'neutral',
    validator: (v) => ['success', 'warning', 'info', 'danger', 'neutral', 'purple'].includes(v),
  },
  dot: {
    type: Boolean,
    default: false,
  },
});

const map = {
  success: { bg: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' },
  warning: { bg: 'bg-amber-100 text-amber-700', dot: 'bg-amber-500' },
  info: { bg: 'bg-blue-100 text-blue-700', dot: 'bg-blue-500' },
  danger: { bg: 'bg-rose-100 text-rose-700', dot: 'bg-rose-500' },
  neutral: { bg: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' },
  purple: { bg: 'bg-purple-100 text-purple-700', dot: 'bg-purple-500' },
};

const classes = computed(() => [
  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
  map[props.variant].bg,
]);
const dotColor = computed(() => map[props.variant].dot);
</script>
