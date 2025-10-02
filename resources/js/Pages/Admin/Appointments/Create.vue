<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">事前アポイント新規登録</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- 会社名 -->
            <div>
              <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                会社名 <span class="text-red-500">*</span>
              </label>
              <input
                id="company_name"
                v-model="form.company_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.company_name }}
              </p>
            </div>

            <!-- 訪問者名 -->
            <div>
              <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-1">
                訪問者名 <span class="text-red-500">*</span>
              </label>
              <input
                id="visitor_name"
                v-model="form.visitor_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_name }}
              </p>
            </div>

            <!-- メールアドレス -->
            <div>
              <label for="visitor_email" class="block text-sm font-medium text-gray-700 mb-1">
                メールアドレス
              </label>
              <input
                id="visitor_email"
                v-model="form.visitor_email"
                type="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_email" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_email }}
              </p>
            </div>

            <!-- 電話番号 -->
            <div>
              <label for="visitor_phone" class="block text-sm font-medium text-gray-700 mb-1">
                電話番号
              </label>
              <input
                id="visitor_phone"
                v-model="form.visitor_phone"
                type="tel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_phone" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_phone }}
              </p>
            </div>

            <!-- 担当スタッフ -->
            <div>
              <label for="staff_member_id" class="block text-sm font-medium text-gray-700 mb-1">
                担当スタッフ <span class="text-red-500">*</span>
              </label>
              <select
                id="staff_member_id"
                v-model="form.staff_member_id"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">選択してください</option>
                <option v-for="staff in staffMembers" :key="staff.id" :value="staff.id">
                  {{ staff.name }} ({{ staff.department }})
                </option>
              </select>
              <p v-if="form.errors.staff_member_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.staff_member_id }}
              </p>
            </div>

            <!-- 訪問日 -->
            <div>
              <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-1">
                訪問日 <span class="text-red-500">*</span>
              </label>
              <input
                id="visit_date"
                v-model="form.visit_date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visit_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.visit_date }}
              </p>
            </div>

            <!-- 訪問時刻 -->
            <div>
              <label for="visit_time" class="block text-sm font-medium text-gray-700 mb-1">
                訪問時刻 <span class="text-red-500">*</span>
              </label>
              <input
                id="visit_time"
                v-model="form.visit_time"
                type="time"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visit_time" class="mt-1 text-sm text-red-600">
                {{ form.errors.visit_time }}
              </p>
            </div>

            <!-- 訪問目的 -->
            <div>
              <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">
                訪問目的
              </label>
              <textarea
                id="purpose"
                v-model="form.purpose"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">
                {{ form.errors.purpose }}
              </p>
            </div>

            <!-- ボタン -->
            <div class="flex justify-end space-x-3 pt-4">
              <Link 
                :href="route('admin.appointments.index')" 
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
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  staffMembers: Array,
});

const form = useForm({
  company_name: '',
  visitor_name: '',
  visitor_email: '',
  visitor_phone: '',
  staff_member_id: '',
  visit_date: '',
  visit_time: '',
  purpose: '',
});

const submit = () => {
  form.post(route('admin.appointments.store'));
};
</script>



