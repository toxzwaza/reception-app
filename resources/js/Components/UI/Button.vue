<template>
  <component
    :is="component"
    :type="!href ? type : undefined"
    :href="href"
    :class="buttonClasses"
    :disabled="disabled"
  >
    <slot name="icon-left"></slot>
    <slot></slot>
    <slot name="icon-right"></slot>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline', 'danger', 'success'].includes(value)
  },
  size: {
    type: String,
    default: 'base',
    validator: (value) => ['sm', 'base', 'lg'].includes(value)
  },
  fullWidth: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  href: {
    type: String,
    default: null
  }
});

// href がある場合は Link コンポーネント、ない場合は button 要素を使用
const component = computed(() => props.href ? Link : 'button');

// 共通のクラスを計算
const buttonClasses = computed(() => [
  'inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-base font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
  props.variant === 'primary' && 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
  props.variant === 'secondary' && 'bg-gray-800 text-white hover:bg-gray-900 focus:ring-gray-700',
  props.variant === 'outline' && 'border-2 border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
  props.variant === 'danger' && 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
  props.variant === 'success' && 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
  props.fullWidth && 'w-full',
  props.disabled && 'opacity-50 cursor-not-allowed',
  props.size === 'sm' && 'px-4 py-2 text-sm',
  props.size === 'lg' && 'px-8 py-4 text-lg',
]);
</script>
