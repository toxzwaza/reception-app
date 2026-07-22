<template>
  <div class="space-y-6">
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">パターン名 <span class="text-rose-500">*</span></label>
      <input v-model="form.name" type="text" required placeholder="例: 本社受付 / 工場入口" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
      <div v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">説明</label>
      <input v-model="form.description" type="text" placeholder="設置場所の補足など" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
      <div v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-2">表示する導線</label>
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
        <label
          v-for="opt in featureOptions"
          :key="opt.key"
          class="flex items-center gap-3 rounded-lg border px-4 py-3 cursor-pointer transition"
          :class="form.features.includes(opt.key) ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white hover:bg-slate-50'"
        >
          <input type="checkbox" :value="opt.key" v-model="form.features" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-sm font-medium text-slate-700">{{ opt.label }}</span>
        </label>
      </div>
      <p class="mt-1 text-xs text-slate-500">チェックした導線のみ受付トップに表示されます。</p>
      <div v-if="form.errors.features" class="mt-1 text-sm text-rose-600">{{ form.errors.features }}</div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">表示順</label>
        <input v-model.number="form.sort_order" type="number" min="0" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        <p class="mt-1 text-xs text-slate-500">小さいほど選択リストの上に表示されます。</p>
        <div v-if="form.errors.sort_order" class="mt-1 text-sm text-rose-600">{{ form.errors.sort_order }}</div>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-sm font-medium text-slate-700">有効（受付端末の選択肢に表示する）</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  featureOptions: { type: Array, default: () => [] },
});
</script>
