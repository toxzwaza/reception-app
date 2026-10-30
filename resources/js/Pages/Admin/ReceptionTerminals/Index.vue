<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          受付端末
        </h2>
        <Link
          :href="route('admin.reception-terminals.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <p class="text-sm text-slate-600">
          受付タブレットで一度だけ <code class="rounded bg-slate-100 px-1.5 py-0.5 text-slate-700">/?terminal=番号</code> を開くと、その端末番号がタブレットに保存されます。
          来客の受付時に、端末番号に紐付いたカメラを事務所ディスプレイで30秒全画面表示し、音楽サーバーでチャイムを鳴らします。
        </p>

        <!-- 端末一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">端末番号</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">設置場所</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">全画面にするカメラ</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="terminal in terminals" :key="terminal.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ terminal.number }}</td>
                  <td class="px-6 py-4 text-sm text-slate-700">{{ terminal.name }}</td>
                  <td class="px-6 py-4 text-sm text-slate-700">{{ cameraLabel(terminal.camera_src) }}</td>
                  <td class="px-6 py-4 text-sm text-center">
                    <Badge v-if="terminal.enabled" variant="success" dot>有効</Badge>
                    <Badge v-else variant="neutral">無効</Badge>
                  </td>
                  <td class="px-6 py-4 text-sm text-right">
                    <Link :href="route('admin.reception-terminals.edit', terminal.id)" class="text-blue-600 hover:text-blue-800 font-medium">
                      編集
                    </Link>
                  </td>
                </tr>
                <tr v-if="terminals.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-slate-400">受付端末が登録されていません。</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 既定カメラ -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-lg font-semibold text-slate-800">既定カメラ</h3>
          <p class="mt-1 text-sm text-slate-600">端末番号が未設定、または上の一覧に登録されていない端末で受付したときに全画面にするカメラです。</p>
          <form @submit.prevent="saveDefaultCamera" class="mt-4 flex flex-wrap items-center gap-3">
            <select
              v-model="defaultForm.default_camera"
              class="rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option v-for="camera in cameras" :key="camera.src" :value="camera.src">
                {{ camera.label }}（{{ camera.src }}）
              </option>
            </select>
            <button
              type="submit"
              :disabled="defaultForm.processing || !defaultForm.isDirty"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 transition"
            >
              保存
            </button>
            <div v-if="defaultForm.errors.default_camera" class="w-full text-sm text-rose-600">{{ defaultForm.errors.default_camera }}</div>
          </form>
        </div>

        <!-- チャイム音量 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-lg font-semibold text-slate-800">チャイム音量</h3>
          <p class="mt-1 text-sm text-slate-600">来客時に音楽サーバーで鳴らすチャイムの音量です（0〜100）。保存すると数秒後の来客から反映されます。鳴り終わると音楽は元の音量に戻ります。</p>
          <form @submit.prevent="saveChimeVolume" class="mt-4 flex flex-wrap items-center gap-3">
            <input
              v-model.number="volumeForm.chime_volume"
              type="range"
              min="0"
              max="100"
              step="5"
              class="w-64 accent-blue-600"
            />
            <input
              v-model.number="volumeForm.chime_volume"
              type="number"
              min="0"
              max="100"
              class="w-24 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <button
              type="submit"
              :disabled="volumeForm.processing || !volumeForm.isDirty"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 transition"
            >
              保存
            </button>
            <div v-if="volumeForm.errors.chime_volume" class="w-full text-sm text-rose-600">{{ volumeForm.errors.chime_volume }}</div>
          </form>
        </div>

        <!-- 直近の来客イベント -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="px-6 pt-5 pb-3">
            <h3 class="text-lg font-semibold text-slate-800">直近の来客イベント（50件）</h3>
            <p class="mt-1 text-sm text-slate-600">中継役（音楽サーバー）が受け取った結果です。90日を過ぎたものは自動で削除されます。</p>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">ID</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">発生時刻</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">種別</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">端末</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">カメラ</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">表示文字</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">抑止</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">表示</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">チャイム</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">受取時刻</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="event in events" :key="event.id">
                  <td class="px-4 py-3 text-slate-500 tabular-nums">{{ event.id }}</td>
                  <td class="px-4 py-3 text-slate-700 whitespace-nowrap tabular-nums">{{ event.occurred_at }}</td>
                  <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ typeLabels[event.type] || event.type }}</td>
                  <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ event.terminal ?? '未設定' }}</td>
                  <td class="px-4 py-3 text-slate-700">{{ event.camera }}</td>
                  <td class="px-4 py-3 text-slate-700 min-w-[16rem]">{{ event.message }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <Badge v-if="event.suppressed" variant="warning">{{ reasonLabels[event.suppress_reason] || event.suppress_reason }}</Badge>
                  </td>
                  <td class="px-4 py-3"><Badge v-if="event.ack_display" :variant="ackVariant(event.ack_display)">{{ event.ack_display }}</Badge></td>
                  <td class="px-4 py-3"><Badge v-if="event.ack_chime" :variant="ackVariant(event.ack_chime)">{{ event.ack_chime }}</Badge></td>
                  <td class="px-4 py-3 text-slate-500 whitespace-nowrap tabular-nums" :title="event.ack_detail || ''">
                    {{ event.acked_at || '未受信' }}
                  </td>
                </tr>
                <tr v-if="events.length === 0">
                  <td colspan="10" class="px-6 py-10 text-center text-slate-400">来客イベントはまだありません。</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  terminals: { type: Array, default: () => [] },
  cameras: { type: Array, default: () => [] },
  defaultCamera: { type: String, default: 'nvr1' },
  chimeVolume: { type: Number, default: 60 },
  events: { type: Array, default: () => [] },
});

const typeLabels = {
  appointment: 'アポイントあり',
  walk_in: 'アポイントなし',
  interview: '面接',
};

const reasonLabels = {
  interview_cooldown: '面接2分以内',
  terminal_disabled: '端末無効',
  rate_limited: '連続操作',
};

const cameraLabel = (src) => {
  const camera = props.cameras.find((c) => c.src === src);
  return camera ? `${camera.label}（${src}）` : src;
};

const ackVariant = (value) => ({ ok: 'success', fail: 'danger', skip: 'neutral' }[value] || 'neutral');

const defaultForm = useForm({
  default_camera: props.defaultCamera,
});

const saveDefaultCamera = () => {
  defaultForm.post(route('admin.reception-terminals.default-camera'), {
    preserveScroll: true,
    onSuccess: () => defaultForm.defaults(),
  });
};

const volumeForm = useForm({
  chime_volume: props.chimeVolume,
});

const saveChimeVolume = () => {
  volumeForm.post(route('admin.reception-terminals.chime-volume'), {
    preserveScroll: true,
    onSuccess: () => volumeForm.defaults(),
  });
};
</script>
