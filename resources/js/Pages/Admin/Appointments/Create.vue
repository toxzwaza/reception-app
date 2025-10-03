<template>
  <AdminLayout>
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
                  {{ staff.name }}
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

    <!-- メール送信確認ダイアログ -->
    <div v-if="showEmailDialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">メール送信確認</h3>
          <div class="mt-4">
            <p class="text-sm text-gray-500">
              訪問者に受付番号とQRコードを含む確認メールを送信しますか？
            </p>
            <p class="text-xs text-gray-400 mt-2">
              送信先: {{ form.visitor_email }}
            </p>
          </div>
          <div class="flex justify-center space-x-4 mt-6">
            <button
              @click="skipEmail"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm"
            >
              送信しない
            </button>
            <button
              @click="confirmSendEmail"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
            >
              メール送信
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

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
  send_email: false,
});

const showEmailDialog = ref(false);

const submit = () => {
  // メールアドレスが入力されている場合は送信確認ダイアログを表示
  if (form.visitor_email) {
    showEmailDialog.value = true;
  } else {
    // メールアドレスがない場合は直接送信
    form.post(route('admin.appointments.store'));
  }
};

const confirmSendEmail = () => {
  showEmailDialog.value = false;
  // フォームデータにsend_emailを追加
  form.send_email = true;
  console.log('Sending with email:', form.data());
  form.post(route('admin.appointments.store'));
};

const skipEmail = () => {
  showEmailDialog.value = false;
  // フォームデータにsend_emailを追加
  form.send_email = false;
  console.log('Sending without email:', form.data());
  form.post(route('admin.appointments.store'));
};
</script>



