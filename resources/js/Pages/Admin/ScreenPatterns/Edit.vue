<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        画面パターン 編集
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <PatternFields :form="form" :feature-options="featureOptions" />

            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link :href="route('admin.screen-patterns.index')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition">
                キャンセル
              </Link>
              <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition">
                更新する
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PatternFields from './PatternFields.vue';

const props = defineProps({
  pattern: { type: Object, required: true },
  featureOptions: { type: Array, default: () => [] },
});

const form = useForm({
  name: props.pattern.name,
  description: props.pattern.description || '',
  features: props.pattern.features || [],
  sort_order: props.pattern.sort_order ?? 0,
  is_active: !!props.pattern.is_active,
});

const submit = () => {
  form.put(route('admin.screen-patterns.update', props.pattern.id));
};
</script>
