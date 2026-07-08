<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-baseline gap-3">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          ダッシュボード
        </h2>
        <span class="hidden sm:inline text-sm text-slate-400">{{ todayLabel }}</span>
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

        <!-- 統計カード（KPI） -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <StatCard title="本日のアポイント" :value="stats.todayAppointments ?? 0" unit="件" color="blue" :href="route('admin.appointments.index')">
            <template #icon><span class="text-xl">📅</span></template>
          </StatCard>
          <StatCard title="未チェックイン" :value="stats.todayNotCheckedIn ?? 0" unit="件" color="amber" :href="route('admin.appointments.index')">
            <template #icon><span class="text-xl">⏳</span></template>
          </StatCard>
          <StatCard title="本日の会議室予定" :value="stats.todayRoomEvents ?? 0" unit="件" color="purple" :href="route('admin.facility-reservations.index')">
            <template #icon><span class="text-xl">🏢</span></template>
          </StatCard>
          <StatCard title="未押印の納品書" :value="stats.unsealedDeliveries ?? 0" unit="件" color="rose" :href="route('admin.deliveries.index')">
            <template #icon><span class="text-xl">📄</span></template>
          </StatCard>
          <StatCard title="未押印の集荷伝票" :value="stats.unsealedPickups ?? 0" unit="件" color="rose" :href="route('admin.pickups.index')">
            <template #icon><span class="text-xl">🚚</span></template>
          </StatCard>
          <StatCard title="未納品の発注データ" :value="stats.pendingOrders ?? 0" unit="件" color="cyan" :href="route('admin.deliveries.index')">
            <template #icon><span class="text-xl">📦</span></template>
          </StatCard>
        </div>

        <!-- 本日のチェックイン進捗 -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-slate-700">本日のチェックイン進捗</span>
            <span class="text-sm text-slate-500">
              <span class="font-bold text-slate-800">{{ stats.todayCheckedIn ?? 0 }}</span>
              / {{ stats.todayAppointments ?? 0 }} 名
              <span class="ml-1 text-slate-400">({{ checkinPercent }}%)</span>
            </span>
          </div>
          <div class="h-2.5 w-full rounded-full bg-slate-100 overflow-hidden">
            <div
              class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
              :style="{ width: checkinPercent + '%' }"
            ></div>
          </div>
        </div>

        <!-- 要対応パネル -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <SectionHeader title="要対応" :subtitle="`${totalActionCount}件`">
            <template #icon>⚠️</template>
          </SectionHeader>

          <div v-if="totalActionCount === 0" class="text-center py-8 text-slate-400">
            対応が必要な項目はありません 🎉
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <!-- 未押印の納品書 -->
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-semibold text-slate-700">未押印の納品書</span>
                <Badge :variant="actionItems.unsealedDeliveries.length ? 'danger' : 'neutral'">
                  {{ actionItems.unsealedDeliveries.length }}
                </Badge>
              </div>
              <div v-if="actionItems.unsealedDeliveries.length" class="space-y-1">
                <Link
                  v-for="d in actionItems.unsealedDeliveries"
                  :key="'ud' + d.id"
                  :href="route('admin.deliveries.show', d.id)"
                  class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm hover:bg-rose-50 transition"
                >
                  <span class="truncate text-slate-700">#{{ d.id }} {{ d.delivery_type }}</span>
                  <span class="shrink-0 text-xs text-slate-400 tabular-nums">{{ fmtDateTime(d.received_at) }}</span>
                </Link>
              </div>
              <p v-else class="text-sm text-slate-400 px-3 py-2">なし</p>
            </div>

            <!-- 未押印の集荷伝票 -->
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-semibold text-slate-700">未押印の集荷伝票</span>
                <Badge :variant="actionItems.unsealedPickups.length ? 'danger' : 'neutral'">
                  {{ actionItems.unsealedPickups.length }}
                </Badge>
              </div>
              <div v-if="actionItems.unsealedPickups.length" class="space-y-1">
                <Link
                  v-for="p in actionItems.unsealedPickups"
                  :key="'up' + p.id"
                  :href="route('admin.pickups.show', p.id)"
                  class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm hover:bg-rose-50 transition"
                >
                  <span class="truncate text-slate-700">#{{ p.id }} 集荷伝票</span>
                  <span class="shrink-0 text-xs text-slate-400 tabular-nums">{{ fmtDateTime(p.picked_up_at) }}</span>
                </Link>
              </div>
              <p v-else class="text-sm text-slate-400 px-3 py-2">なし</p>
            </div>

            <!-- 紐づけ未完了の納品書 -->
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-semibold text-slate-700">紐づけ未完了の納品書</span>
                <Badge :variant="actionItems.unlinkedDeliveries.length ? 'warning' : 'neutral'">
                  {{ actionItems.unlinkedDeliveries.length }}
                </Badge>
              </div>
              <div v-if="actionItems.unlinkedDeliveries.length" class="space-y-1">
                <Link
                  v-for="d in actionItems.unlinkedDeliveries"
                  :key="'ul' + d.id"
                  :href="route('admin.deliveries.show', d.id)"
                  class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm hover:bg-amber-50 transition"
                >
                  <span class="truncate text-slate-700">#{{ d.id }} {{ d.delivery_type }}</span>
                  <span class="shrink-0 text-xs text-slate-400 tabular-nums">{{ fmtDateTime(d.received_at) }}</span>
                </Link>
              </div>
              <p v-else class="text-sm text-slate-400 px-3 py-2">なし</p>
            </div>

            <!-- 時刻超過の未チェックイン -->
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-semibold text-slate-700">時刻超過の未チェックイン</span>
                <Badge :variant="actionItems.overdueAppointments.length ? 'danger' : 'neutral'">
                  {{ actionItems.overdueAppointments.length }}
                </Badge>
              </div>
              <div v-if="actionItems.overdueAppointments.length" class="space-y-1">
                <Link
                  v-for="ap in actionItems.overdueAppointments"
                  :key="'oa' + ap.id"
                  :href="route('admin.appointments.edit', ap.id)"
                  class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm hover:bg-rose-50 transition"
                >
                  <span class="truncate text-slate-700">
                    <span class="font-semibold text-rose-600 tabular-nums mr-1">{{ formatTime(ap.visit_time) }}</span>
                    {{ ap.company_name }}
                  </span>
                  <span class="shrink-0 text-xs text-slate-400 truncate">{{ ap.staff_member?.name || '—' }}</span>
                </Link>
              </div>
              <p v-else class="text-sm text-slate-400 px-3 py-2">なし</p>
            </div>
          </div>
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

        <!-- 2カラム：最近の納品・集荷 / 週次サマリー -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- 最近の納品・集荷 -->
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <SectionHeader title="最近の納品書・集荷伝票" />
            <div v-if="recentDocuments.length" class="relative pl-2">
              <div
                v-for="doc in recentDocuments"
                :key="doc.kind + doc.id"
                class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-b-0"
              >
                <span
                  :class="['flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-base', doc.kind === 'delivery' ? 'bg-blue-100' : 'bg-purple-100']"
                >
                  {{ doc.kind === 'delivery' ? '📄' : '🚚' }}
                </span>
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-slate-800 truncate">
                    #{{ doc.id }} {{ doc.label }}
                  </div>
                  <div class="text-xs text-slate-400 tabular-nums">{{ fmtDateTime(doc.datetime) }}</div>
                </div>
                <Badge :variant="doc.sealed ? 'success' : 'warning'" dot>
                  {{ doc.sealed ? '電子印済み' : '未押印' }}
                </Badge>
                <Link :href="docHref(doc)" class="shrink-0 text-sm text-blue-600 hover:text-blue-800">詳細</Link>
              </div>
            </div>
            <div v-else class="text-center py-10 text-slate-400">まだ書類がありません</div>
          </div>

          <!-- 週次サマリー -->
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <SectionHeader title="週次サマリー" subtitle="過去7日間" />
            <!-- 凡例 -->
            <div class="flex items-center gap-4 mb-4 text-xs text-slate-500">
              <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-blue-500"></span>来訪</span>
              <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>納品</span>
              <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>集荷</span>
            </div>
            <!-- グラフ -->
            <div class="flex items-end justify-between gap-2 h-40">
              <div
                v-for="(day, i) in weekly"
                :key="i"
                class="flex flex-1 flex-col items-center gap-1"
              >
                <div class="flex items-end justify-center gap-0.5 w-full" style="height: 120px;">
                  <div
                    class="w-1/3 max-w-[10px] rounded-t bg-blue-500 transition-all duration-500"
                    :style="{ height: barHeight(day.visits) }"
                    :title="`来訪 ${day.visits}件`"
                  ></div>
                  <div
                    class="w-1/3 max-w-[10px] rounded-t bg-emerald-500 transition-all duration-500"
                    :style="{ height: barHeight(day.deliveries) }"
                    :title="`納品 ${day.deliveries}件`"
                  ></div>
                  <div
                    class="w-1/3 max-w-[10px] rounded-t bg-amber-500 transition-all duration-500"
                    :style="{ height: barHeight(day.pickups) }"
                    :title="`集荷 ${day.pickups}件`"
                  ></div>
                </div>
                <div :class="['text-[11px] leading-tight text-center', day.isToday ? 'font-bold text-blue-600' : 'text-slate-400']">
                  {{ day.weekday }}<br />{{ day.label }}
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import Badge from '@/Components/UI/Badge.vue';
import SectionHeader from '@/Components/UI/SectionHeader.vue';

const props = defineProps({
  announcements: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  todayAppointmentList: { type: Array, default: () => [] },
  roomSchedules: { type: Array, default: () => [] },
  actionItems: {
    type: Object,
    default: () => ({ unsealedDeliveries: [], unsealedPickups: [], unlinkedDeliveries: [], overdueAppointments: [] }),
  },
  recentDocuments: { type: Array, default: () => [] },
  weekly: { type: Array, default: () => [] },
});

const todayLabel = new Intl.DateTimeFormat('ja-JP', {
  year: 'numeric', month: 'long', day: 'numeric', weekday: 'short',
}).format(new Date());

// チェックイン進捗（%）
const checkinPercent = computed(() => {
  const total = props.stats.todayAppointments ?? 0;
  if (!total) return 0;
  return Math.round(((props.stats.todayCheckedIn ?? 0) / total) * 100);
});

// 要対応の合計件数
const totalActionCount = computed(() => {
  const a = props.actionItems;
  return (a.unsealedDeliveries?.length ?? 0)
    + (a.unsealedPickups?.length ?? 0)
    + (a.unlinkedDeliveries?.length ?? 0)
    + (a.overdueAppointments?.length ?? 0);
});

// 週次グラフのスケール（全系列の最大値）
const weeklyMax = computed(() => {
  const vals = props.weekly.flatMap((d) => [d.visits, d.deliveries, d.pickups]);
  return Math.max(1, ...vals);
});
const barHeight = (v) => {
  if (!v) return '2px';
  return Math.max(6, Math.round((v / weeklyMax.value) * 100)) + '%';
};

// 最近の書類の詳細リンク
const docHref = (doc) =>
  doc.kind === 'delivery'
    ? route('admin.deliveries.show', doc.id)
    : route('admin.pickups.show', doc.id);

const alertClass = (type) =>
  type === 'error' ? 'bg-rose-50 border-rose-500'
  : type === 'warning' ? 'bg-amber-50 border-amber-500'
  : 'bg-blue-50 border-blue-500';

const formatTime = (t) => (t ? String(t).slice(0, 5) : '');
const fmtDateTime = (v) =>
  v ? new Intl.DateTimeFormat('ja-JP', { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).format(new Date(v)) : '';
const cleanTitle = (t) => (t ? t.replace(/^\[\d+\]/, '') : '');
</script>
