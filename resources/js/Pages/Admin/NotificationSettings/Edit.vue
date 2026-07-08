<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          通知設定編集: {{ notificationSetting.name }}
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('admin.notification-settings.index')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← 一覧に戻る
          </Link>
          <Link
            :href="route('admin.notification-settings.show', notificationSetting.id)"
            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            詳細
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <!-- 基本情報 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">基本情報</h3>
                <div class="grid grid-cols-1 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                      通知名 <span class="text-rose-500">*</span>
                    </label>
                    <input 
                      type="text" 
                      v-model="form.name"
                      class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                    <div v-if="errors.name" class="mt-1 text-sm text-rose-600">{{ errors.name }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">説明</label>
                    <textarea 
                      v-model="form.description"
                      rows="3"
                      class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                    <div v-if="errors.description" class="mt-1 text-sm text-rose-600">{{ errors.description }}</div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                      トリガーイベント <span class="text-rose-500">*</span>
                    </label>
                    <select 
                      v-model="form.trigger_event"
                      class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                      <option value="">選択してください</option>
                      <option v-for="(label, value) in triggerEvents" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <div v-if="errors.trigger_event" class="mt-1 text-sm text-rose-600">{{ errors.trigger_event }}</div>
                  </div>

                  <div>
                    <label class="flex items-center">
                      <input
                        type="checkbox"
                        v-model="form.is_active"
                        class="rounded border-slate-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                      >
                      <span class="ml-2 text-sm text-slate-700">有効</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- 通知受信者 -->
              <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-semibold text-slate-800">通知受信者</h3>
                  <button
                    type="button"
                    @click="addRecipient"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition"
                  >
                    ＋ 受信者を追加
                  </button>
                </div>

                <div v-if="form.recipients.length === 0" class="text-center py-8 text-slate-500">
                  通知受信者が設定されていません
                </div>

                <div v-for="(recipient, index) in form.recipients" :key="index" class="border border-slate-200 rounded-xl p-4 mb-4">
                  <div class="flex justify-between items-start mb-4">
                    <h4 class="font-medium text-slate-800">受信者 {{ index + 1 }}</h4>
                    <button
                      type="button"
                      @click="removeRecipient(index)"
                      class="text-rose-600 hover:text-rose-800 text-sm"
                    >
                      削除
                    </button>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">
                        スタッフメンバー <span class="text-rose-500">*</span>
                      </label>
                      <select 
                        v-model="recipient.staff_member_id"
                        @change="onStaffMemberChange(index)"
                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                      >
                        <option value="">選択してください</option>
                        <option v-for="staffMember in staffMembers" :key="staffMember.id" :value="staffMember.id">
                          {{ staffMember.user?.name || 'N/A' }} ({{ staffMember.user?.email || 'N/A' }})
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">
                        通知方法 <span class="text-rose-500">*</span>
                      </label>
                      <select 
                        v-model="recipient.notification_type"
                        @change="onNotificationTypeChange(index)"
                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                      >
                        <option value="">選択してください</option>
                        <option v-for="(label, value) in notificationTypes" :key="value" :value="value">
                          {{ label }}
                        </option>
                      </select>
                      <p v-if="recipient.notification_type" class="mt-1 text-xs text-slate-500">
                        {{ { teams: 'Teamsでメンションする相手のメールアドレスを入力', email: '通知メールの送信先アドレスを入力（実際にメールを送信）', phone: '発信する電話番号を入力（受付端末から発信）' }[recipient.notification_type] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">
                        通知データ <span class="text-rose-500">*</span>
                      </label>
                      <input 
                        type="text" 
                        v-model="recipient.notification_data"
                        :placeholder="getNotificationDataPlaceholder(recipient.notification_type)"
                        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required
                      >
                    </div>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                <Link
                  :href="route('admin.notification-settings.index')"
                  class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
                >
                  キャンセル
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition"
                >
                  {{ processing ? '更新中...' : '更新' }}
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
import { reactive, ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  notificationSetting: Object,
  triggerEvents: Object,
  notificationTypes: Object,
  staffMembers: Array,
  errors: Object,
});

const form = reactive({
  name: props.notificationSetting.name,
  description: props.notificationSetting.description || '',
  trigger_event: props.notificationSetting.trigger_event,
  is_active: props.notificationSetting.is_active,
  recipients: []
});

const processing = ref(false);

// 既存の受信者データをフォームに設定
onMounted(() => {
  if (props.notificationSetting.recipients) {
    form.recipients = props.notificationSetting.recipients.map(recipient => {
      // スタッフメンバーIDを取得（user_idから逆引き）
      const staffMember = props.staffMembers.find(sm => sm.user && sm.user.id === recipient.user_id);
      return {
        staff_member_id: staffMember ? staffMember.id : '',
        notification_type: recipient.notification_type,
        notification_data: recipient.notification_data,
      };
    });
  }
  
  // 受信者が空の場合は初期値を追加
  if (form.recipients.length === 0) {
    form.recipients.push({
      staff_member_id: '',
      notification_type: '',
      notification_data: '',
    });
  }
});

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
  
  router.put(route('admin.notification-settings.update', props.notificationSetting.id), form, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>
