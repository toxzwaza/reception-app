<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          ダッシュボード
        </h2>
        <span class="text-sm text-slate-400">{{ todayLabel }}</span>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- お知らせ -->
        <div v-if="announcements.length" class="space-y-3">
          <div
            v-for="a in announcements"
            :key="a.id"
            :class="['rounded-xl border-l-4 p-4 shadow-sm', alertClass(a.type)]"
          >
            <h4 class="font-bold mb-1 text-slate-800">{{ a.title }}</h4>
            <p class="text-sm whitespace-pre-wrap text-slate-700">{{ a.content }}</p>
          </div>
        </div>

        <!-- 統計カード -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard title="本日のアポイント" :value="stats.todayAppointments ?? 0" unit="件" color="blue" :href="route('admin.appointments.index')">
            <template #icon><span class="text-xl">📅</span></template>
          </StatCard>
          <StatCard title="チェックイン済" :value="stats.todayCheckedIn ?? 0" unit="件" color="emerald">
            <template #icon><span class="text-xl">✅</span></template>
          </StatCard>
          <StatCard title="未チェックイン" :value="stats.pendingAppointments ?? 0" unit="件" color="amber">
            <template #icon><span class="text-xl">⏳</span></template>
          </StatCard>
          <StatCard title="本日の会議室予定" :value="stats.todayRoomEvents ?? 0" unit="件" color="purple" :href="route('admin.facility-reservations.index')">
            <template #icon><span class="text-xl">🏢</span></template>
          </StatCard>
        </div>

        <!-- 2カラム：本日のアポイント / 会議室スケジュール -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- 本日のアポイント -->
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <SectionHeader title="本日のアポイント" :subtitle="`${todayAppointmentList.length}件`">
              <template #action>
                <Link :href="route('admin.appointments.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">すべて見る →</Link>
              </template>
            </SectionHeader>
            <div v-if="todayAppointmentList.length" class="space-y-2 max-h-96 overflow-y-auto">
              <div
                v-for="ap in todayAppointmentList"
                :key="ap.id"
                class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-blue-50/50 transition"
              >
                <div class="flex-shrink-0 w-14 text-center">
                  <div class="text-lg font-bold text-blue-700 tabular-nums">{{ formatTime(ap.visit_time) }}</div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-slate-800 truncate">{{ ap.company_name }}</div>
                  <div class="text-xs text-slate-500 truncate">{{ ap.visitor_name }}様 ／ 担当: {{ ap.staff_member?.name || '—' }}</div>
                </div>
                <Badge :variant="ap.is_checked_in ? 'success' : 'warning'" dot>
                  {{ ap.is_checked_in ? 'チェックイン済' : '未' }}
                </Badge>
              </div>
            </div>
            <div v-else class="text-center py-10 text-slate-400">本日のアポイントはありません</div>
          </div>

          <!-- 会議室スケジュール -->
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <SectionHeader title="本日の会議室スケジュール">
              <template #action>
                <Link :href="route('admin.facilities.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">施設管理 →</Link>
              </template>
            </SectionHeader>
            <div class="space-y-4 max-h-96 overflow-y-auto">
              <div v-for="room in roomSchedules" :key="room.id">
                <div class="flex items-center gap-2 mb-1">
                  <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                  <span class="font-semibold text-slate-700 text-sm">{{ room.name }}</span>
                  <span class="text-xs text-slate-400">{{ room.schedule_events.length }}件</span>
                </div>
                <div v-if="room.schedule_events.length" class="pl-4 space-y-1">
                  <div v-for="ev in room.schedule_events" :key="ev.id" class="flex items-center gap-2 text-sm">
                    <span class="text-blue-700 font-medium tabular-nums whitespace-nowrap">{{ ev.start_datetime }}-{{ ev.end_datetime }}</span>
                    <span class="text-slate-700 truncate">{{ cleanTitle(ev.title) }}</span>
                    <Badge v-if="ev.badge" variant="info">{{ ev.badge }}</Badge>
                  </div>
                </div>
                <div v-else class="pl-4 text-xs text-slate-400">予定なし</div>
              </div>
              <div v-if="!roomSchedules.length" class="text-center py-6 text-slate-400">施設が登録されていません</div>
            </div>
          </div>
        </div>

        <!-- クイックアクション -->
        <div>
          <SectionHeader title="クイックアクション" />
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <Link
              v-for="q in quickActions"
              :key="q.route"
              :href="route(q.route)"
              class="group bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex items-center gap-3"
            >
              <span :class="['w-10 h-10 rounded-lg flex items-center justify-center text-lg group-hover:scale-110 transition-transform', q.chip]">{{ q.icon }}</span>
              <span class="text-sm font-medium text-slate-700">{{ q.label }}</span>
            </Link>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import Badge from '@/Components/UI/Badge.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';

defineProps({
  announcements: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  todayAppointmentList: { type: Array, default: () => [] },
  roomSchedules: { type: Array, default: () => [] },
});

const todayLabel = new Intl.DateTimeFormat('ja-JP', {
  year: 'numeric', month: 'long', day: 'numeric', weekday: 'short',
}).format(new Date());

const quickActions = [
  { label: 'アポイント', route: 'admin.appointments.index', icon: '📅', chip: 'bg-blue-100' },
  { label: '施設予約', route: 'admin.facility-reservations.index', icon: '🗓️', chip: 'bg-purple-100' },
  { label: '施設管理', route: 'admin.facilities.index', icon: '🏢', chip: 'bg-cyan-100' },
  { label: 'スタッフ', route: 'admin.staff-members.index', icon: '👥', chip: 'bg-emerald-100' },
  { label: 'お知らせ', route: 'admin.announcements.index', icon: '📢', chip: 'bg-amber-100' },
  { label: '通知設定', route: 'admin.notification-settings.index', icon: '🔔', chip: 'bg-rose-100' },
  { label: '納品書', route: 'admin.deliveries.index', icon: '📦', chip: 'bg-blue-100' },
  { label: '集荷伝票', route: 'admin.pickups.index', icon: '🚚', chip: 'bg-purple-100' },
];

const alertClass = (type) =>
  type === 'error' ? 'bg-rose-50 border-rose-500'
  : type === 'warning' ? 'bg-amber-50 border-amber-500'
  : 'bg-blue-50 border-blue-500';

const formatTime = (t) => (t ? String(t).slice(0, 5) : '');
const cleanTitle = (t) => (t ? t.replace(/^\[\d+\]/, '') : '');
</script>
