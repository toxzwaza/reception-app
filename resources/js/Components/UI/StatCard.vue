<template>
  <component
    :is="href ? Link : 'div'"
    :href="href || undefined"
    :class="[
      'relative overflow-hidden bg-white rounded-2xl border border-slate-200 p-5 shadow-sm transition-all duration-300',
      href ? 'hover:shadow-lg hover:-translate-y-0.5 cursor-pointer group block' : '',
    ]"
  >
    <!-- 装飾の半透明丸 -->
    <div :class="['absolute -mr-10 -mt-10 top-0 right-0 w-24 h-24 rounded-full opacity-10', accent.solid]"></div>

    <div class="relative flex items-center gap-4">
      <!-- アイコンチップ -->
      <div :class="['flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300', accent.chip, href ? 'group-hover:scale-110' : '']">
        <slot name="icon">
          <span :class="['text-xl', accent.text]">📊</span>
        </slot>
      </div>

      <div class="min-w-0">
        <div class="text-sm font-medium text-slate-500 truncate">{{ title }}</div>
        <div class="text-2xl font-bold text-slate-800 leading-tight">
          {{ value }}<span v-if="unit" class="text-base font-medium text-slate-400 ml-1">{{ unit }}</span>
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: 0 },
  unit: { type: String, default: '' },
  color: {
    type: String,
    default: 'blue',
    validator: (v) => ['blue', 'cyan', 'emerald', 'amber', 'purple', 'rose'].includes(v),
  },
  href: { type: String, default: null },
});

const accents = {
  blue: { chip: 'bg-blue-100', text: 'text-blue-600', solid: 'bg-blue-500' },
  cyan: { chip: 'bg-cyan-100', text: 'text-cyan-600', solid: 'bg-cyan-500' },
  emerald: { chip: 'bg-emerald-100', text: 'text-emerald-600', solid: 'bg-emerald-500' },
  amber: { chip: 'bg-amber-100', text: 'text-amber-600', solid: 'bg-amber-500' },
  purple: { chip: 'bg-purple-100', text: 'text-purple-600', solid: 'bg-purple-500' },
  rose: { chip: 'bg-rose-100', text: 'text-rose-600', solid: 'bg-rose-500' },
};

const accent = computed(() => accents[props.color]);
</script>
