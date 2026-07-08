<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        部署電話番号編集
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <h3 class="text-lg font-semibold text-slate-800 border-b border-slate-200 pb-2">{{ department.name }}</h3>

            <!-- 電話番号 -->
            <div>
              <label for="phone_number" class="block text-sm font-medium text-slate-700 mb-1">
                電話番号
              </label>
              <input
                id="phone_number"
                v-model="form.phone_number"
                type="tel"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="+81xxxxxxxxxx または 0xx-xxxx-xxxx"
              />
              <p class="text-xs text-slate-500 mt-1">
                ※ アポなし来訪時に受付端末からこの番号へ自動発信します。空欄にすると、その部署は受付の部署選択に表示されません。
              </p>
              <div v-if="form.errors.phone_number" class="text-sm text-rose-600 mt-1">{{ form.errors.phone_number }}</div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link
                :href="route('admin.departments.index')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50 font-semibold transition"
              >
                更新
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

const props = defineProps({
  department: { type: Object, required: true },
});

const form = useForm({
  phone_number: props.department.phone_number || '',
});

const submit = () => {
  form.put(route('admin.departments.update', props.department.id));
};
</script>
