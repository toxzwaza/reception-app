<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">部署電話番号編集</h2>
    </template>

    <div class="py-12">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">{{ department.name }}</h3>

            <!-- 電話番号 -->
            <div>
              <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                電話番号
              </label>
              <input
                id="phone_number"
                v-model="form.phone_number"
                type="tel"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="+81xxxxxxxxxx または 0xx-xxxx-xxxx"
              />
              <p class="text-xs text-gray-500 mt-1">
                ※ アポなし来訪時に受付端末からこの番号へ自動発信します。空欄にすると、その部署は受付の部署選択に表示されません。
              </p>
              <div v-if="form.errors.phone_number" class="text-sm text-red-600 mt-1">{{ form.errors.phone_number }}</div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4">
              <Link
                :href="route('admin.departments.index')"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md disabled:opacity-50 font-semibold"
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
