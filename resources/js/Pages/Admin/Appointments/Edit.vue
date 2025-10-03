<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">事前アポイント編集</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- 受付番号（表示のみ） -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                受付番号
              </label>
              <div class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md text-gray-700">
                {{ appointment.reception_number }}
              </div>
            </div>

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

            <!-- チェックイン状態 -->
            <div v-if="appointment.is_checked_in" class="bg-green-50 border border-green-200 rounded-md p-4">
              <p class="text-sm text-green-800">
                <strong>チェックイン済み:</strong> {{ formatDateTime(appointment.checked_in_at) }}
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
  appointment: Object,
  staffMembers: Array,
});

const form = useForm({
  company_name: props.appointment.company_name,
  visitor_name: props.appointment.visitor_name,
  visitor_email: props.appointment.visitor_email,
  visitor_phone: props.appointment.visitor_phone,
  staff_member_id: props.appointment.staff_member_id,
  visit_date: props.appointment.visit_date,
  visit_time: props.appointment.visit_time,
  purpose: props.appointment.purpose,
});

const submit = () => {
  form.put(route('admin.appointments.update', props.appointment.id));
};

const formatDateTime = (dateTime) => {
  return new Date(dateTime).toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>



