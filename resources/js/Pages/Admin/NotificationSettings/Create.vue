<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">通知設定作成</h2>
        <Link 
          :href="route('admin.notification-settings.index')"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
        >
          一覧に戻る
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <!-- 基本情報 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">基本情報</h3>
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      通知名 <span class="text-red-500">*</span>
                    </label>
                    <input 
                      type="text" 
                      v-model="form.name"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="例: 納品書受信通知"
                      required
                    >
                    <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                    <textarea 
                      v-model="form.description"
                      rows="3"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="通知の詳細説明（任意）"
                    ></textarea>
                    <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      トリガーイベント <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="form.trigger_event"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      required
                    >
                      <option value="">選択してください</option>
                      <option v-for="(label, value) in triggerEvents" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <div v-if="errors.trigger_event" class="mt-1 text-sm text-red-600">{{ errors.trigger_event }}</div>
                  </div>

                  <div>
                    <label class="flex items-center">
                      <input 
                        type="checkbox" 
                        v-model="form.is_active"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      >
                      <span class="ml-2 text-sm text-gray-700">有効にする</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- 通知受信者 -->
              <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-semibold text-gray-900">通知受信者</h3>
                  <button 
                    type="button"
                    @click="addRecipient"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                  >
                    受信者を追加
                  </button>
                </div>

                <div v-if="form.recipients.length === 0" class="text-center py-8 text-gray-500">
                  通知受信者が設定されていません
                </div>

                <div v-for="(recipient, index) in form.recipients" :key="index" class="border border-gray-200 rounded-lg p-4 mb-4">
                  <div class="flex justify-between items-start mb-4">
                    <h4 class="font-medium text-gray-900">受信者 {{ index + 1 }}</h4>
                    <button 
                      type="button"
                      @click="removeRecipient(index)"
                      class="text-red-600 hover:text-red-800 text-sm"
                    >
                      削除
                    </button>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        スタッフメンバー <span class="text-red-500">*</span>
                      </label>
                      <select 
                        v-model="recipient.staff_member_id"
                        @change="onStaffMemberChange(index)"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                      >
                        <option value="">選択してください</option>
                        <option v-for="staffMember in staffMembers" :key="staffMember.id" :value="staffMember.id">
                          {{ staffMember.user?.name || 'N/A' }} ({{ staffMember.user?.email || 'N/A' }})
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        通知方法 <span class="text-red-500">*</span>
                      </label>
                      <select 
                        v-model="recipient.notification_type"
                        @change="onNotificationTypeChange(index)"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                      >
                        <option value="">選択してください</option>
                        <option v-for="(label, value) in notificationTypes" :key="value" :value="value">
                          {{ label }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        通知データ <span class="text-red-500">*</span>
                      </label>
                      <input 
                        type="text" 
                        v-model="recipient.notification_data"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :placeholder="getNotificationDataPlaceholder(recipient.notification_type)"
                        required
                      >
                    </div>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-4">
                <Link 
                  :href="route('admin.notification-settings.index')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  キャンセル
                </Link>
                <button 
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  {{ processing ? '作成中...' : '作成' }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  triggerEvents: Object,
  notificationTypes: Object,
  staffMembers: Array,
  errors: Object,
});

const form = reactive({
  name: '',
  description: '',
  trigger_event: '',
  is_active: true,
  recipients: [
    {
      staff_member_id: '',
      notification_type: '',
      notification_data: '',
    }
  ]
});

const processing = ref(false);

// 通知データのプレースホルダーを取得
const getNotificationDataPlaceholder = (type) => {
  switch (type) {
    case 'phone':
      return '例: 090-1234-5678';
    case 'email':
      return '例: user@example.com';
    case 'teams':
      return '例: @username または Webhook URL';
    default:
      return '通知データを入力してください';
  }
};

// 受信者を追加
const addRecipient = () => {
  form.recipients.push({
    staff_member_id: '',
    notification_type: '',
    notification_data: '',
  });
};

// 受信者を削除
const removeRecipient = (index) => {
  if (form.recipients.length > 1) {
    form.recipients.splice(index, 1);
  }
};

// 通知タイプ変更時の処理
const onNotificationTypeChange = (index) => {
  const recipient = form.recipients[index];
  const staffMember = props.staffMembers.find(sm => sm.id == recipient.staff_member_id);
  
  switch (recipient.notification_type) {
    case 'phone':
      recipient.notification_data = '';
      break;
    case 'email':
      recipient.notification_data = staffMember?.user?.email || '';
      break;
    case 'teams':
      recipient.notification_data = staffMember?.user?.email || '';
      break;
  }
};

// スタッフメンバー変更時の処理
const onStaffMemberChange = (index) => {
  const recipient = form.recipients[index];
  const staffMember = props.staffMembers.find(sm => sm.id == recipient.staff_member_id);
  
  if (staffMember) {
    switch (recipient.notification_type) {
      case 'email':
        recipient.notification_data = staffMember.user?.email || '';
        break;
      case 'teams':
        recipient.notification_data = staffMember.user?.email || '';
        break;
    }
  }
};

// フォーム送信
const submit = () => {
  processing.value = true;
  
  router.post(route('admin.notification-settings.store'), form, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>
