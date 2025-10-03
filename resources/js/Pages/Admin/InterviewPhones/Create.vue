<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">面接時の通話先電話番号 新規登録</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- 部署名 -->
            <div>
              <label for="department_name" class="block text-sm font-medium text-gray-700 mb-1">
                部署名 <span class="text-red-500">*</span>
              </label>
              <input
                id="department_name"
                v-model="form.department_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.department_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.department_name }}
              </p>
            </div>

            <!-- 担当者名 -->
            <div>
              <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                担当者名 <span class="text-red-500">*</span>
              </label>
              <input
                id="contact_person"
                v-model="form.contact_person"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.contact_person" class="mt-1 text-sm text-red-600">
                {{ form.errors.contact_person }}
              </p>
            </div>

            <!-- 電話番号 -->
            <div>
              <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                電話番号 <span class="text-red-500">*</span>
              </label>
              <input
                id="phone_number"
                v-model="form.phone_number"
                type="tel"
                required
                placeholder="例: 03-1234-5678"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">
                {{ form.errors.phone_number }}
              </p>
            </div>

            <!-- 内線番号 -->
            <div>
              <label for="extension_number" class="block text-sm font-medium text-gray-700 mb-1">
                内線番号
              </label>
              <input
                id="extension_number"
                v-model="form.extension_number"
                type="text"
                placeholder="例: 1234"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.extension_number" class="mt-1 text-sm text-red-600">
                {{ form.errors.extension_number }}
              </p>
            </div>

            <!-- 備考 -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                備考
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                {{ form.errors.notes }}
              </p>
            </div>

            <!-- 表示順 -->
            <div>
              <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">
                表示順
              </label>
              <input
                id="display_order"
                v-model.number="form.display_order"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p class="mt-1 text-sm text-gray-500">小さい数字ほど上に表示されます</p>
              <p v-if="form.errors.display_order" class="mt-1 text-sm text-red-600">
                {{ form.errors.display_order }}
              </p>
            </div>

            <!-- 有効/無効 -->
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-700">
                有効
              </label>
            </div>

            <!-- ボタン -->
            <div class="flex justify-end space-x-3 pt-4">
              <Link 
                :href="route('admin.interview-phones.index')" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50"
              >
                登録
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

const form = useForm({
  department_name: '',
  contact_person: '',
  phone_number: '',
  extension_number: '',
  notes: '',
  is_active: true,
  display_order: 0,
});

const submit = () => {
  form.post(route('admin.interview-phones.store'));
};
</script>



