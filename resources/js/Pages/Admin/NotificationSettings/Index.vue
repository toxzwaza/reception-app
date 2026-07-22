<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          通知設定管理
        </h2>
        <div class="flex gap-2">
          <button
            type="button"
            @click="sendTestGeneral"
            :disabled="testSending"
            class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
            title="メンションなしのシンプルなテスト通知を Teams に送信"
          >
            {{ testSending ? '送信中...' : '🧪 Teams テスト送信' }}
          </button>
          <Link
            :href="route('admin.dashboard')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← ダッシュボード
          </Link>
          <Link
            :href="route('admin.notification-settings.create')"
            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
          >
            ＋ 新規作成
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        <div v-if="$page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
          {{ $page.props.flash.success }}
        </div>

        <!-- タクシー会社電話番号 -->
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-800">🚕 タクシー会社電話番号</h3>
          <p class="mt-1 mb-3 text-sm text-slate-500">
            受付画面の「タクシーを呼ぶ」ボタンから、この番号へ受付端末から発信します。
          </p>
          <form @submit.prevent="saveTaxi" class="flex flex-wrap items-center gap-3">
            <input
              v-model="taxiForm.phone_number"
              type="tel"
              placeholder="例: 0864-00-0000"
              class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-72"
            />
            <button
              type="submit"
              :disabled="taxiForm.processing"
              class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50"
            >
              {{ taxiForm.processing ? '保存中...' : '保存' }}
            </button>
            <span v-if="taxiForm.errors.phone_number" class="text-sm text-rose-600">{{ taxiForm.errors.phone_number }}</span>
          </form>
        </div>

        <!-- 通知設定一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">通知名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">トリガーイベント</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">受信者数</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">作成日</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="setting in notificationSettings" :key="setting.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-semibold text-slate-800">{{ setting.name }}</div>
                    <div v-if="setting.description" class="text-sm text-slate-500">{{ setting.description }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge variant="info">{{ triggerEvents[setting.trigger_event] }}</Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ setting.recipients.length }}名
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge :variant="setting.is_active ? 'success' : 'danger'" dot>
                      {{ setting.is_active ? '有効' : '無効' }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                    {{ formatDate(setting.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                    <div class="flex justify-end gap-3">
                      <Link
                        :href="route('admin.notification-settings.show', setting.id)"
                        class="text-blue-600 hover:text-blue-800"
                      >
                        詳細
                      </Link>
                      <Link
                        :href="route('admin.notification-settings.edit', setting.id)"
                        class="text-blue-600 hover:text-blue-800"
                      >
                        編集
                      </Link>
                      <button
                        @click="toggleSetting(setting.id)"
                        :class="setting.is_active ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800'"
                      >
                        {{ setting.is_active ? '無効化' : '有効化' }}
                      </button>
                      <button
                        @click="sendTestForSetting(setting.id, setting.name)"
                        :disabled="testSending"
                        class="text-purple-600 hover:text-purple-800 disabled:text-slate-400"
                        title="この設定の受信者にテスト通知を送信"
                      >
                        🧪 テスト
                      </button>
                      <button
                        @click="deleteSetting(setting.id)"
                        class="text-rose-600 hover:text-rose-800"
                      >
                        削除
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 空の状態 -->
          <div v-if="notificationSettings.length === 0" class="text-center py-16">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-slate-700">通知設定がありません</h3>
            <p class="mt-1 text-sm text-slate-500">新しい通知設定を作成してください。</p>
            <div class="mt-6">
              <Link
                :href="route('admin.notification-settings.create')"
                class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
              >
                ＋ 新規作成
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  notificationSettings: Array,
  triggerEvents: Object,
  notificationTypes: Object,
  taxiPhone: { type: String, default: '' },
});

// タクシー会社電話番号の設定フォーム
const taxiForm = useForm({ phone_number: props.taxiPhone });
const saveTaxi = () => {
  taxiForm.post(route('admin.notification-settings.taxi'), { preserveScroll: true });
};

// テスト送信中フラグ（多重押下防止）
const testSending = ref(false);

// テスト送信（設定ID指定 or 全般）
const runTestSend = async (notificationSettingId = null, label = '全般') => {
  if (testSending.value) return;
  testSending.value = true;
  try {
    const { data } = await axios.post(
      route('admin.notification-settings.test-send'),
      notificationSettingId ? { notification_setting_id: notificationSettingId } : {},
    );
    alert(`✅ ${label}: ${data.message}${data.mention_count ? `\nメンション: ${data.mention_count}名` : ''}`);
  } catch (error) {
    const msg = error.response?.data?.message || error.message || '不明なエラー';
    alert(`❌ ${label}: テスト送信に失敗しました\n${msg}`);
    console.error(error);
  } finally {
    testSending.value = false;
  }
};

const sendTestGeneral = () => runTestSend(null, '全般テスト');
const sendTestForSetting = (id, name) => runTestSend(id, `${name} のテスト`);

// 日付フォーマット
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// 通知設定の有効/無効切り替え
const toggleSetting = (settingId) => {
  if (confirm('通知設定の状態を変更しますか？')) {
    router.post(route('admin.notification-settings.toggle', settingId), {}, {
      preserveScroll: true,
    });
  }
};

// 通知設定の削除
const deleteSetting = (settingId) => {
  if (confirm('この通知設定を削除しますか？\n\nこの操作は取り消せません。')) {
    router.delete(route('admin.notification-settings.destroy', settingId));
  }
};
</script>
