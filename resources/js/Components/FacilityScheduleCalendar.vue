<template>
  <div class="facility-schedule-calendar">
    <!-- 施設選択 -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        施設を選択してください <span class="text-red-500">*</span>
      </label>
      <select
        v-model="selectedFacilityId"
        @change="loadSchedules"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      >
        <option value="">施設を選択してください</option>
        <option v-for="facility in facilities" :key="facility.id" :value="facility.id">
          {{ facility.name }}
        </option>
      </select>
    </div>

    <!-- 週の切り替えボタン -->
    <div v-if="selectedFacilityId" class="mb-4 flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
      <button
        type="button"
        @click="previousWeek"
        :disabled="weekOffset === 0"
        class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        前の週
      </button>
      
      <div class="text-sm font-medium text-gray-700">
        {{ weekRangeText }}
      </div>
      
      <button
        type="button"
        @click="nextWeek"
        class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
      >
        次の週
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <!-- 施設が選択されていない場合 -->
    <div v-if="!selectedFacilityId" class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
      <p class="text-gray-600">施設を選択すると、予約可能な時間帯が表示されます</p>
    </div>

    <!-- ローディング中 -->
    <div v-else-if="isLoading" class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
      <div class="flex items-center justify-center">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="text-gray-600">予定を読み込んでいます...</p>
      </div>
    </div>

    <!-- カレンダー表示 -->
    <div v-else class="border border-gray-300 rounded-lg overflow-hidden">
      <!-- ヘッダー：週の日付 -->
      <div class="grid grid-cols-8 bg-gray-100 border-b border-gray-300">
        <div class="p-2 text-center text-xs font-medium text-gray-600">時刻</div>
        <div
          v-for="day in weekDays"
          :key="day.date"
          class="p-2 text-center border-l border-gray-300"
          :class="{ 'bg-blue-50': isToday(day.date) }"
        >
          <div class="text-xs font-medium text-gray-900">{{ day.dayOfWeek }}</div>
          <div class="text-sm font-bold text-gray-700">{{ day.dayMonth }}</div>
        </div>
      </div>

      <!-- 時間軸とカレンダーグリッド -->
      <div class="relative">
        <div class="grid grid-cols-8">
          <!-- 時間ラベル列 -->
          <div class="bg-gray-50">
            <div
              v-for="hour in hours"
              :key="hour"
              class="h-12 flex items-center justify-center text-xs text-gray-600 border-b border-gray-200"
            >
              {{ formatHour(hour) }}
            </div>
          </div>

          <!-- 各日のカレンダーセル -->
          <div
            v-for="day in weekDays"
            :key="day.date"
            class="border-l border-gray-300 relative"
          >
            <!-- 時間グリッド -->
            <div
              v-for="hour in hours"
              :key="`${day.date}-${hour}`"
              class="h-12 border-b border-gray-200 relative cursor-pointer hover:bg-blue-50"
              :data-date="day.date"
              :data-hour="hour"
              @mousedown="startSelection($event, day.date, hour)"
              @mouseenter="updateSelection($event, day.date, hour)"
              @mouseup="endSelection"
            >
              <!-- 30分の区切り線 -->
              <div class="absolute top-1/2 left-0 right-0 border-t border-gray-100"></div>
            </div>

            <!-- 既存の予定を表示 -->
            <div
              v-for="schedule in getSchedulesForDay(day.date)"
              :key="schedule.id"
              class="absolute left-0 right-0 mx-1 bg-red-400 border border-red-500 rounded px-1 z-10 cursor-help group"
              :style="getScheduleStyle(schedule)"
              @mouseenter="showScheduleTooltip(schedule, $event)"
              @mouseleave="hideScheduleTooltip"
            >
              <div class="text-xs text-white font-medium truncate">{{ schedule.title }}</div>
              <div class="text-xs text-white opacity-90">
                {{ formatTime(schedule.start_datetime) }} - {{ formatTime(schedule.end_datetime) }}
              </div>
            </div>

            <!-- 参加者の予定を表示（色分け + 重なり表示） -->
            <div
              v-for="schedule in getSortedParticipantSchedules(day.date)"
              :key="`participant-${schedule.id}`"
              class="absolute mx-1 border-2 rounded px-1 cursor-help opacity-80 hover:opacity-100 transition-opacity"
              :style="getParticipantScheduleStyle(schedule, day.date)"
              @mouseenter="showParticipantScheduleTooltip(schedule, $event)"
              @mouseleave="hideParticipantScheduleTooltip"
            >
              <div class="text-xs text-white font-medium truncate">{{ schedule.user?.name || 'ユーザー' }}</div>
              <div class="text-xs text-white opacity-90">
                {{ formatTime(schedule.start_datetime) }} - {{ formatTime(schedule.end_datetime) }}
              </div>
            </div>
            
            <!-- ホバー時のツールチップ（予定ブロックの外に配置） -->
            <div
              v-for="schedule in getSchedulesForDay(day.date)"
              :key="`tooltip-${schedule.id}`"
              v-show="hoveredSchedule && hoveredSchedule.id === schedule.id"
              class="absolute bg-white text-gray-900 text-xs rounded-lg shadow-2xl p-4 min-w-[300px] max-w-[320px] z-[100] border-2 border-indigo-200"
              :style="getTooltipStyle(schedule, day.date)"
            >
              <div class="space-y-3">
                <!-- タイトル -->
                <div class="flex items-start gap-2">
                  <div class="flex-shrink-0 w-1 h-full bg-indigo-500 rounded"></div>
                  <div class="font-bold text-base text-indigo-900 leading-tight">
                    {{ schedule.title }}
                  </div>
                </div>
                
                <!-- 詳細情報 -->
                <div class="space-y-2 pl-3">
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">日付</div>
                      <div class="font-medium text-gray-900">{{ formatDate(schedule.date) }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">時間</div>
                      <div class="font-medium text-gray-900">
                        {{ formatTime(schedule.start_datetime) }} - {{ formatTime(schedule.end_datetime) }}
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="schedule.badge" class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">バッジ</div>
                      <span class="inline-block bg-gradient-to-r from-amber-400 to-yellow-400 text-gray-900 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                        {{ schedule.badge }}
                      </span>
                    </div>
                  </div>
                  
                  <!-- 参加者情報 -->
                  <div v-if="schedule.participants && schedule.participants.length > 0" class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <div class="flex-1">
                      <div class="text-xs text-gray-500 mb-1">参加者 ({{ schedule.participants.length }}名)</div>
                      <div class="flex flex-wrap gap-1.5">
                        <div
                          v-for="participant in schedule.participants"
                          :key="participant.id"
                          class="inline-flex items-center gap-1 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-full px-2.5 py-1 text-xs"
                          :title="participant.email || ''"
                        >
                          <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                          <span class="font-medium text-gray-900">{{ participant.name }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 参加者予定のツールチップ -->
            <div
              v-for="schedule in getSortedParticipantSchedules(day.date)"
              :key="`participant-tooltip-${schedule.id}`"
              v-show="hoveredParticipantSchedule && hoveredParticipantSchedule.id === schedule.id"
              class="absolute bg-white text-gray-900 text-xs rounded-lg shadow-2xl p-4 min-w-[300px] max-w-[320px] z-[100] border-2"
              :style="getParticipantTooltipStyle(schedule, day.date)"
            >
              <div class="space-y-3">
                <!-- タイトル -->
                <div class="flex items-start gap-2">
                  <div 
                    class="flex-shrink-0 w-1 h-full rounded" 
                    :style="{ backgroundColor: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }"
                  ></div>
                  <div class="font-bold text-base leading-tight" :style="{ color: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }">
                    {{ schedule.title }}
                  </div>
                </div>
                
                <!-- 詳細情報 -->
                <div class="space-y-2 pl-3">
                  <!-- ユーザー名 -->
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" :style="{ color: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">ユーザー</div>
                      <div class="font-medium text-gray-900">{{ schedule.user?.name || 'ユーザー' }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" :style="{ color: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">日付</div>
                      <div class="font-medium text-gray-900">{{ formatDate(schedule.date) }}</div>
                    </div>
                  </div>
                  
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" :style="{ color: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">時間</div>
                      <div class="font-medium text-gray-900">
                        {{ formatTime(schedule.start_datetime) }} - {{ formatTime(schedule.end_datetime) }}
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="schedule.badge" class="flex items-start gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" :style="{ color: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <div>
                      <div class="text-xs text-gray-500">バッジ</div>
                      <span 
                        class="inline-block px-3 py-1 rounded-full text-xs font-bold shadow-sm text-white"
                        :style="{ backgroundColor: (props.userColorMap[schedule.user_id]?.hex || '#6b7280') }"
                      >
                        {{ schedule.badge }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ドラッグ中の一時選択表示 -->
            <div
              v-if="isSelecting && selectionDate === day.date"
              class="absolute left-0 right-0 mx-1 bg-green-400 bg-opacity-50 border-2 border-green-500 rounded z-20 pointer-events-none"
              :style="getSelectionStyle()"
            >
              <div class="text-xs text-green-900 font-bold text-center mt-1">選択中</div>
            </div>
            
            <!-- 確定された選択を表示 -->
            <div
              v-if="!isSelecting && selectedTimeSlot && selectedTimeSlot.date === day.date"
              class="absolute left-0 right-0 mx-1 bg-green-500 bg-opacity-60 border-2 border-green-600 rounded z-20 pointer-events-none"
              :style="getConfirmedSelectionStyle()"
            >
              <div class="text-xs text-white font-bold text-center mt-1">✓ 選択済み</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 選択された時間帯の表示と編集 -->
      <div v-if="selectedTimeSlot" class="bg-green-50 border-t border-gray-300 p-4">
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900">選択された時間帯</h4>
            <button
              type="button"
              @click="clearSelection"
              class="text-sm text-red-600 hover:text-red-800 font-medium"
            >
              ✕ クリア
            </button>
          </div>
          
          <div class="grid grid-cols-3 gap-3 items-center">
            <!-- 日付表示 -->
            <div class="text-sm text-gray-700">
              <span class="font-medium">日付:</span>
              <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded">
                {{ selectedTimeSlot.date }}
              </div>
            </div>
            
            <!-- 開始時刻編集 -->
            <div class="text-sm text-gray-700">
              <label class="font-medium block mb-1">開始時刻:</label>
              <input
                v-model="selectedTimeSlot.start_time"
                type="time"
                @change="updateTimeSelection"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:border-green-500 focus:ring-green-500"
              />
            </div>
            
            <!-- 終了時刻編集 -->
            <div class="text-sm text-gray-700">
              <label class="font-medium block mb-1">終了時刻:</label>
              <input
                v-model="selectedTimeSlot.end_time"
                type="time"
                @change="updateTimeSelection"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:border-green-500 focus:ring-green-500"
              />
            </div>
          </div>
          
          <div class="text-xs text-gray-500 italic">
            ※ 時刻を直接編集できます。変更すると自動的に反映されます。
          </div>
        </div>
      </div>

      <!-- 予定情報 -->
      <div class="bg-gray-50 border-t border-gray-300 p-3">
        <div class="flex items-center justify-between text-xs text-gray-600">
          <span>📅 この期間の予定: <strong>{{ schedules.length }}件</strong></span>
          <span v-if="schedules.length === 0" class="text-amber-600">※ この期間には予定がありません</span>
        </div>
      </div>
    </div>

    <!-- 操作説明 -->
    <div class="mt-3 text-xs text-gray-600">
      <p>💡 ヒント: マウスをドラッグして時間帯を選択してください。赤色のエリアは予約済みです。</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  facilities: {
    type: Array,
    required: true,
  },
  participantSchedules: {
    type: Array,
    default: () => [],
  },
  userColorMap: {
    type: Object,
    default: () => ({}),
  },
  modelValue: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue']);

// 施設選択
const selectedFacilityId = ref('');

// スケジュールデータ
const schedules = ref([]);

// ローディング状態
const isLoading = ref(false);

// 週のオフセット（0=今週、1=来週、2=再来週...）
const weekOffset = ref(0);

// 時間範囲（8:00 - 20:00）
const hours = Array.from({ length: 13 }, (_, i) => i + 8); // 8, 9, 10, ..., 20

// 表示する週の日付を生成
const weekDays = computed(() => {
  const days = [];
  const today = new Date();
  const startDate = new Date(today);
  startDate.setDate(today.getDate() + (weekOffset.value * 7));
  
  for (let i = 0; i < 7; i++) {
    const date = new Date(startDate);
    date.setDate(startDate.getDate() + i);
    
    const dateStr = date.toISOString().split('T')[0];
    const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()];
    const dayMonth = `${date.getMonth() + 1}/${date.getDate()}`;
    
    days.push({
      date: dateStr,
      dayOfWeek,
      dayMonth,
    });
  }
  
  return days;
});

// 週の範囲テキスト
const weekRangeText = computed(() => {
  if (weekDays.value.length === 0) return '';
  const start = weekDays.value[0].dayMonth;
  const end = weekDays.value[6].dayMonth;
  return `${start} 〜 ${end}`;
});

// ドラッグ選択の状態
const isSelecting = ref(false);
const selectionDate = ref('');
const selectionStartHour = ref(0);
const selectionEndHour = ref(0);
const selectedTimeSlot = ref(null);

// ツールチップ表示用
const hoveredSchedule = ref(null);
const hoveredParticipantSchedule = ref(null);

// 施設のスケジュールを読み込む
const loadSchedules = async () => {
  if (!selectedFacilityId.value) {
    schedules.value = [];
    return;
  }

  isLoading.value = true;
  console.log('Loading schedules for facility:', selectedFacilityId.value);
  console.log('Date range:', weekDays.value[0].date, 'to', weekDays.value[6].date);

  try {
    const response = await window.axios.get(`/admin/facilities/${selectedFacilityId.value}/schedule`, {
      params: {
        start_date: weekDays.value[0].date,
        end_date: weekDays.value[6].date,
      },
    });
    console.log('API Response:', response.data);
    console.log('Schedules loaded:', response.data.schedules);
    console.log('Schedule count:', response.data.schedules?.length || 0);
    
    schedules.value = response.data.schedules || [];
    
    if (schedules.value.length === 0) {
      console.warn('No schedules found for this facility in the selected date range.');
    }
  } catch (error) {
    console.error('Failed to load schedules:', error);
    console.error('Error details:', error.response?.data);
    console.error('Error status:', error.response?.status);
    schedules.value = [];
    alert(`予定の読み込みに失敗しました。\nエラー: ${error.message}\nステータス: ${error.response?.status || 'unknown'}`);
  } finally {
    isLoading.value = false;
  }
};

// 指定日のスケジュールを取得
const getSchedulesForDay = (date) => {
  const filtered = schedules.value.filter(schedule => {
    // dateカラムから日付を取得（ISO8601形式の場合は日付部分のみ抽出）
    let scheduleDate = schedule.date;
    if (scheduleDate && scheduleDate.includes('T')) {
      // ISO8601形式の場合（例: "2025-11-05T15:00:00.000000Z"）
      scheduleDate = scheduleDate.split('T')[0];
    }
    const matches = scheduleDate === date;
    
    if (matches) {
      console.log(`Schedule found for ${date}:`, {
        title: schedule.title,
        date: scheduleDate,
        start: schedule.start_datetime,
        end: schedule.end_datetime
      });
    }
    
    return matches;
  });
  
  console.log(`Total schedules for ${date}: ${filtered.length}`);
  return filtered;
};

// 指定日の参加者予定を取得
const getParticipantSchedulesForDay = (date) => {
  if (!props.participantSchedules || props.participantSchedules.length === 0) {
    return [];
  }
  
  return props.participantSchedules.filter(schedule => {
    let scheduleDate = schedule.date;
    if (scheduleDate && scheduleDate.includes('T')) {
      scheduleDate = scheduleDate.split('T')[0];
    }
    return scheduleDate === date;
  });
};

// 同じ日の参加者予定を時間順にソート
const getSortedParticipantSchedules = (date) => {
  const schedules = getParticipantSchedulesForDay(date);
  return schedules.sort((a, b) => {
    const [aHours, aMinutes] = a.start_datetime.split(':').map(Number);
    const [bHours, bMinutes] = b.start_datetime.split(':').map(Number);
    return (aHours + aMinutes / 60) - (bHours + bMinutes / 60);
  });
};

// 2つの予定が重なっているか判定
const isOverlapping = (schedule1, schedule2) => {
  const [start1Hours, start1Minutes] = schedule1.start_datetime.split(':').map(Number);
  const [end1Hours, end1Minutes] = schedule1.end_datetime.split(':').map(Number);
  const [start2Hours, start2Minutes] = schedule2.start_datetime.split(':').map(Number);
  const [end2Hours, end2Minutes] = schedule2.end_datetime.split(':').map(Number);
  
  const start1 = start1Hours + start1Minutes / 60;
  const end1 = end1Hours + end1Minutes / 60;
  const start2 = start2Hours + start2Minutes / 60;
  const end2 = end2Hours + end2Minutes / 60;
  
  return !(end1 <= start2 || start1 >= end2);
};

// 参加者予定の重なりレベルを計算
const getScheduleOverlapLevel = (schedule, date) => {
  const allSchedules = getSortedParticipantSchedules(date);
  const currentIndex = allSchedules.findIndex(s => s.id === schedule.id);
  
  if (currentIndex === -1) return 0;
  
  let overlapLevel = 0;
  for (let i = 0; i < currentIndex; i++) {
    if (isOverlapping(allSchedules[i], schedule)) {
      overlapLevel++;
    }
  }
  
  return overlapLevel;
};

// 参加者予定の表示スタイルを計算（色分け + 重なり表示）
const getParticipantScheduleStyle = (schedule, date) => {
  const startTimeStr = schedule.start_datetime;
  const endTimeStr = schedule.end_datetime;
  
  const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
  const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
  
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  const topOffset = (startHour - 8) * 48;
  const height = (endHour - startHour) * 48;
  
  // 重なりレベルに応じてサイズを調整
  const overlapLevel = getScheduleOverlapLevel(schedule, date);
  const widthPercentage = overlapLevel > 0 ? 67 : 100; // 重なっている場合は2/3サイズ
  const leftOffset = overlapLevel > 0 ? `${100 - widthPercentage}%` : '0';
  
  // ユーザーの色を取得
  const userColor = props.userColorMap[schedule.user_id];
  
  return {
    top: `${topOffset}px`,
    height: `${height}px`,
    width: `${widthPercentage}%`,
    left: leftOffset,
    backgroundColor: userColor?.hex || '#9ca3af',
    borderColor: userColor?.hex || '#6b7280',
  };
};

// スケジュールの表示スタイルを計算
const getScheduleStyle = (schedule) => {
  // dateカラムから日付を取得
  let dateStr = schedule.date;
  if (dateStr && dateStr.includes('T')) {
    dateStr = dateStr.split('T')[0];
  }
  
  // start_datetimeとend_datetimeは時刻のみの文字列（例: "10:30"）
  const startTimeStr = schedule.start_datetime;
  const endTimeStr = schedule.end_datetime;
  
  console.log('Calculating style for schedule:', {
    title: schedule.title,
    date: dateStr,
    startTimeStr,
    endTimeStr
  });
  
  // 時刻をパースして時間を計算
  const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
  const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
  
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  const topOffset = (startHour - 8) * 48; // 48px per hour
  const height = (endHour - startHour) * 48;
  
  console.log('Style calculated:', {
    startHour,
    endHour,
    topOffset: `${topOffset}px`,
    height: `${height}px`
  });
  
  return {
    top: `${topOffset}px`,
    height: `${height}px`,
  };
};

// ツールチップの位置スタイルを計算
const getTooltipStyle = (schedule, dayDate) => {
  const startTimeStr = schedule.start_datetime;
  const endTimeStr = schedule.end_datetime;
  const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
  const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  const scheduleTopOffset = (startHour - 8) * 48;
  const scheduleHeight = (endHour - startHour) * 48;
  
  // 日付から曜日のインデックスを取得（0=最初の日、6=最後の日）
  const dayIndex = weekDays.value.findIndex(d => d.date === dayDate);
  
  // 予定が下部にあるかどうかを判定（17時以降の予定）
  const isBottomSchedule = startHour >= 17;
  
  // 右端2列の場合は左側に表示、それ以外は右側に表示
  let leftPosition = '100%'; // デフォルトは予定の右側
  let transformX = 'translateX(8px)'; // 少し右にずらす
  
  if (dayIndex >= 5) { // 右端2列
    leftPosition = 'auto';
    transformX = 'translateX(-8px)'; // 少し左にずらす
  }
  
  const styles = {
    left: leftPosition,
    right: dayIndex >= 5 ? '100%' : 'auto',
    transform: transformX,
  };
  
  // 縦方向の位置を決定
  if (isBottomSchedule) {
    // 下部の予定：ツールチップを予定の上に表示
    styles.bottom = `calc(100% - ${scheduleTopOffset}px)`;
    styles.marginBottom = '8px';
  } else {
    // 上部の予定：ツールチップを予定の上端に表示
    styles.top = `${scheduleTopOffset}px`;
    styles.marginTop = '0px';
  }
  
  return styles;
};

// 選択範囲のスタイルを計算
const getSelectionStyle = () => {
  const startHour = Math.min(selectionStartHour.value, selectionEndHour.value);
  const endHour = Math.max(selectionStartHour.value, selectionEndHour.value) + 1;
  
  const topOffset = (startHour - 8) * 48;
  const height = (endHour - startHour) * 48;
  
  return {
    top: `${topOffset}px`,
    height: `${height}px`,
  };
};

// ドラッグ選択開始
const startSelection = (event, date, hour) => {
  if (event.button !== 0) return; // 左クリックのみ
  
  event.preventDefault();
  isSelecting.value = true;
  selectionDate.value = date;
  selectionStartHour.value = hour;
  selectionEndHour.value = hour;
};

// ドラッグ選択更新
const updateSelection = (event, date, hour) => {
  if (!isSelecting.value || selectionDate.value !== date) return;
  
  selectionEndHour.value = hour;
};

// ドラッグ選択終了
const endSelection = () => {
  if (!isSelecting.value) return;
  
  isSelecting.value = false;
  
  // 選択範囲を確定
  const startHour = Math.min(selectionStartHour.value, selectionEndHour.value);
  const endHour = Math.max(selectionStartHour.value, selectionEndHour.value) + 1;
  
  // 重複チェック
  const hasConflict = checkConflict(selectionDate.value, startHour, endHour);
  
  if (hasConflict) {
    alert('選択した時間帯は既に予約されています。別の時間帯を選択してください。');
    return;
  }
  
  // 選択を確定して保持
  selectedTimeSlot.value = {
    date: selectionDate.value,
    start_time: formatHour(startHour),
    end_time: formatHour(endHour),
    start_datetime: `${selectionDate.value} ${formatHour(startHour)}`,
    end_datetime: `${selectionDate.value} ${formatHour(endHour)}`,
    startHour,
    endHour,
  };
  
  // 親コンポーネントに通知
  emitSelection();
};

// 確定された選択のスタイルを計算
const getConfirmedSelectionStyle = () => {
  if (!selectedTimeSlot.value) return {};
  
  // 時刻から時間を計算
  const [startHours, startMinutes] = selectedTimeSlot.value.start_time.split(':').map(Number);
  const [endHours, endMinutes] = selectedTimeSlot.value.end_time.split(':').map(Number);
  
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  const topOffset = (startHour - 8) * 48;
  const height = (endHour - startHour) * 48;
  
  return {
    top: `${topOffset}px`,
    height: `${height}px`,
  };
};

// 時刻の変更を処理
const updateTimeSelection = () => {
  if (!selectedTimeSlot.value) return;
  
  // 時刻の妥当性チェック
  const [startHours, startMinutes] = selectedTimeSlot.value.start_time.split(':').map(Number);
  const [endHours, endMinutes] = selectedTimeSlot.value.end_time.split(':').map(Number);
  
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  // 開始時刻が終了時刻より後の場合
  if (startHour >= endHour) {
    alert('開始時刻は終了時刻より前に設定してください。');
    return;
  }
  
  // 重複チェック
  const hasConflict = checkConflict(selectedTimeSlot.value.date, startHour, endHour);
  
  if (hasConflict) {
    alert('選択した時間帯は既に予約されています。別の時間帯を選択してください。');
    // 元の値に戻す（実装を簡略化するため、再選択を促す）
    return;
  }
  
  // 更新
  selectedTimeSlot.value.start_datetime = `${selectedTimeSlot.value.date} ${selectedTimeSlot.value.start_time}`;
  selectedTimeSlot.value.end_datetime = `${selectedTimeSlot.value.date} ${selectedTimeSlot.value.end_time}`;
  selectedTimeSlot.value.startHour = startHour;
  selectedTimeSlot.value.endHour = endHour;
  
  // 親コンポーネントに通知
  emitSelection();
};

// 選択内容を親コンポーネントに送信
const emitSelection = () => {
  if (!selectedTimeSlot.value) return;
  
  emit('update:modelValue', {
    facility_id: selectedFacilityId.value,
    start_date: selectedTimeSlot.value.date,
    start_time: selectedTimeSlot.value.start_time,
    end_date: selectedTimeSlot.value.date,
    end_time: selectedTimeSlot.value.end_time,
  });
};

// 重複チェック
const checkConflict = (date, startHour, endHour) => {
  // 施設の予定との重複チェック
  const daySchedules = getSchedulesForDay(date);
  
  for (const schedule of daySchedules) {
    // start_datetimeとend_datetimeは時刻のみの文字列（例: "10:30"）
    const startTimeStr = schedule.start_datetime;
    const endTimeStr = schedule.end_datetime;
    
    const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
    const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
    
    const scheduleStartHour = startHours + startMinutes / 60;
    const scheduleEndHour = endHours + endMinutes / 60;
    
    // 重複判定
    if (!(endHour <= scheduleStartHour || startHour >= scheduleEndHour)) {
      return true;
    }
  }
  
  // 参加者の予定との重複チェック
  const participantSchedules = getParticipantSchedulesForDay(date);
  
  for (const schedule of participantSchedules) {
    const startTimeStr = schedule.start_datetime;
    const endTimeStr = schedule.end_datetime;
    
    const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
    const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
    
    const scheduleStartHour = startHours + startMinutes / 60;
    const scheduleEndHour = endHours + endMinutes / 60;
    
    // 重複判定
    if (!(endHour <= scheduleStartHour || startHour >= scheduleEndHour)) {
      return true;
    }
  }
  
  return false;
};

// 選択をクリア
const clearSelection = () => {
  selectedTimeSlot.value = null;
  emit('update:modelValue', null);
};

// 今日かどうか判定
const isToday = (date) => {
  const today = new Date().toISOString().split('T')[0];
  return date === today;
};

// 時刻フォーマット
const formatHour = (hour) => {
  return `${String(Math.floor(hour)).padStart(2, '0')}:${String((hour % 1) * 60).padStart(2, '0')}`;
};

const formatTime = (timeStr) => {
  // 既に時刻形式（"HH:MM"）の場合はそのまま返す
  if (typeof timeStr === 'string' && timeStr.match(/^\d{1,2}:\d{2}$/)) {
    return timeStr;
  }
  // Date型の場合
  const date = new Date(timeStr);
  return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
};

// 日付をフォーマット
const formatDate = (dateStr) => {
  if (!dateStr) return '';
  
  // ISO8601形式の場合は日付部分のみ抽出
  if (dateStr.includes('T')) {
    dateStr = dateStr.split('T')[0];
  }
  
  const date = new Date(dateStr + 'T00:00:00'); // タイムゾーンの影響を避ける
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()];
  
  return `${year}年${month}月${day}日 (${dayOfWeek})`;
};

// スケジュールのツールチップを表示
const showScheduleTooltip = (schedule, event) => {
  console.log('Showing tooltip for:', schedule.title);
  hoveredSchedule.value = schedule;
};

// スケジュールのツールチップを非表示
const hideScheduleTooltip = () => {
  console.log('Hiding tooltip');
  hoveredSchedule.value = null;
};

// 参加者予定のツールチップを表示
const showParticipantScheduleTooltip = (schedule, event) => {
  console.log('Showing participant tooltip for:', schedule.title);
  hoveredParticipantSchedule.value = schedule;
};

// 参加者予定のツールチップを非表示
const hideParticipantScheduleTooltip = () => {
  console.log('Hiding participant tooltip');
  hoveredParticipantSchedule.value = null;
};

// 参加者予定のツールチップ位置スタイルを計算
const getParticipantTooltipStyle = (schedule, dayDate) => {
  const startTimeStr = schedule.start_datetime;
  const endTimeStr = schedule.end_datetime;
  const [startHours, startMinutes] = startTimeStr.split(':').map(Number);
  const [endHours, endMinutes] = endTimeStr.split(':').map(Number);
  const startHour = startHours + startMinutes / 60;
  const endHour = endHours + endMinutes / 60;
  
  const scheduleTopOffset = (startHour - 8) * 48;
  const scheduleHeight = (endHour - startHour) * 48;
  
  // 日付から曜日のインデックスを取得（0=最初の日、6=最後の日）
  const dayIndex = weekDays.value.findIndex(d => d.date === dayDate);
  
  // 予定が下部にあるかどうかを判定（17時以降の予定）
  const isBottomSchedule = startHour >= 17;
  
  // 右端2列の場合は左側に表示、それ以外は右側に表示
  let leftPosition = '100%'; // デフォルトは予定の右側
  let transformX = 'translateX(8px)'; // 少し右にずらす
  
  if (dayIndex >= 5) { // 右端2列
    leftPosition = 'auto';
    transformX = 'translateX(-8px)'; // 少し左にずらす
  }
  
  const styles = {
    left: leftPosition,
    right: dayIndex >= 5 ? '100%' : 'auto',
    transform: transformX,
    borderColor: props.userColorMap[schedule.user_id]?.hex || '#6b7280',
  };
  
  // 縦方向の位置を決定
  if (isBottomSchedule) {
    // 下部の予定：ツールチップを予定の上に表示
    styles.bottom = `calc(100% - ${scheduleTopOffset}px)`;
    styles.marginBottom = '8px';
  } else {
    // 上部の予定：ツールチップを予定の上端に表示
    styles.top = `${scheduleTopOffset}px`;
    styles.marginTop = '0px';
  }
  
  return styles;
};

// グローバルなマウスアップイベントをリッスン
const handleGlobalMouseUp = () => {
  if (isSelecting.value) {
    endSelection();
  }
};

onMounted(() => {
  document.addEventListener('mouseup', handleGlobalMouseUp);
});

onUnmounted(() => {
  document.removeEventListener('mouseup', handleGlobalMouseUp);
});

// 施設が変更されたら選択をクリアして週をリセット
watch(selectedFacilityId, () => {
  selectedTimeSlot.value = null;
  weekOffset.value = 0;
  emit('update:modelValue', null);
});

// 週が変更されたらスケジュールを再読み込み
watch(weekOffset, () => {
  if (selectedFacilityId.value) {
    loadSchedules();
  }
});

// 前の週へ
const previousWeek = () => {
  if (weekOffset.value > 0) {
    weekOffset.value--;
  }
};

// 次の週へ
const nextWeek = () => {
  weekOffset.value++;
};

// 親コンポーネントから最新予定の再取得・選択クリアを呼べるように公開
const refreshSchedules = () => {
  clearSelection();
  loadSchedules();
};
defineExpose({ loadSchedules, refreshSchedules });
</script>

<style scoped>
.facility-schedule-calendar {
  user-select: none;
}
</style>

