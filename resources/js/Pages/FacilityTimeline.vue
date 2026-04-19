<template>
  <Head :title="`施設予約状況 - ${formattedDate}`" />

  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-full mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-800">施設予約状況</h1>
          <div class="flex items-center gap-2">
            <button
              @click="changeDate(-1)"
              class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded text-sm font-medium"
            >
              &larr; 前日
            </button>
            <button
              @click="goToday"
              class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm font-medium"
            >
              今日
            </button>
            <button
              @click="changeDate(1)"
              class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded text-sm font-medium"
            >
              翌日 &rarr;
            </button>
          </div>
        </div>
        <div class="mt-2 text-center">
          <span class="text-lg font-semibold text-gray-700">{{ formattedDate }}</span>
        </div>
      </div>
    </div>

    <!-- Timeline Grid -->
    <div class="max-w-full mx-auto px-4 py-6">
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <div class="min-w-[1100px]">
          <!-- Time Header -->
          <div class="flex border-b border-gray-200">
            <div class="w-36 flex-shrink-0 px-3 py-2 bg-gray-50 font-medium text-sm text-gray-600 border-r">
              施設
            </div>
            <div class="flex-1 flex relative">
              <div
                v-for="hour in hours"
                :key="hour"
                class="flex-1 text-center text-xs text-gray-500 py-2 border-r border-gray-100"
                :style="{ minWidth: hourWidth + 'px' }"
              >
                {{ hour }}:00
              </div>
              <!-- Current time indicator -->
              <div
                v-if="isToday"
                class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-20"
                :style="{ left: currentTimePosition + 'px' }"
              >
                <div class="w-2 h-2 bg-red-500 rounded-full -ml-[3px] -mt-1"></div>
              </div>
            </div>
          </div>

          <!-- Facility Rows -->
          <div
            v-for="(facility, index) in facilities"
            :key="facility.id"
            class="flex border-b border-gray-100 hover:bg-gray-50 transition-colors"
            :class="{ 'bg-gray-25': index % 2 === 1 }"
          >
            <!-- Facility Name -->
            <div class="w-36 flex-shrink-0 px-3 py-3 border-r border-gray-200 flex items-center">
              <div
                class="w-3 h-3 rounded-full mr-2 flex-shrink-0"
                :class="facilityColors[index % facilityColors.length]"
              ></div>
              <span class="text-sm font-medium text-gray-700 truncate">{{ facility.name }}</span>
            </div>

            <!-- Time Slots -->
            <div class="flex-1 flex relative py-1.5" style="min-height: 44px;">
              <!-- Grid lines -->
              <div
                v-for="hour in hours"
                :key="'grid-' + hour"
                class="flex-1 border-r border-gray-50"
                :style="{ minWidth: hourWidth + 'px' }"
              ></div>

              <!-- Event blocks -->
              <div
                v-for="event in facility.events"
                :key="event.id"
                class="absolute top-1.5 bottom-1.5 rounded cursor-pointer transition-opacity hover:opacity-80 flex items-center overflow-hidden"
                :class="facilityBgColors[index % facilityBgColors.length]"
                :style="eventStyle(event)"
                @mouseenter="showTooltip($event, event, facility.name)"
                @mouseleave="hideTooltip"
              >
                <span class="text-xs text-white font-medium px-2 truncate">
                  {{ event.title }}
                </span>
              </div>

              <!-- Current time indicator -->
              <div
                v-if="isToday"
                class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10 pointer-events-none"
                :style="{ left: currentTimePosition + 'px' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
        <div v-for="(facility, index) in facilities" :key="'legend-' + facility.id" class="flex items-center gap-1.5">
          <div
            class="w-3 h-3 rounded"
            :class="facilityBgColors[index % facilityBgColors.length]"
          ></div>
          <span>{{ facility.name }}</span>
        </div>
        <div v-if="isToday" class="flex items-center gap-1.5 ml-4">
          <div class="w-3 h-0.5 bg-red-500"></div>
          <span>現在時刻</span>
        </div>
      </div>
    </div>

    <!-- Tooltip -->
    <div
      v-if="tooltip.visible"
      class="fixed z-50 bg-gray-800 text-white text-sm rounded-lg shadow-lg px-4 py-3 pointer-events-none max-w-xs"
      :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }"
    >
      <div class="font-semibold">{{ tooltip.facility }}</div>
      <div class="mt-1">{{ tooltip.title }}</div>
      <div class="mt-1 text-gray-300">{{ tooltip.time }}</div>
    </div>
  </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  date: String,
  facilities: Array,
});

const START_HOUR = 7;
const END_HOUR = 20;
const hourWidth = 70;

const hours = computed(() => {
  const h = [];
  for (let i = START_HOUR; i < END_HOUR; i++) h.push(i);
  return h;
});

const totalMinutes = (END_HOUR - START_HOUR) * 60;

const formattedDate = computed(() => {
  const d = new Date(props.date + 'T00:00:00');
  const days = ['日', '月', '火', '水', '木', '金', '土'];
  return `${d.getFullYear()}年${d.getMonth() + 1}月${d.getDate()}日（${days[d.getDay()]}）`;
});

const isToday = computed(() => {
  const today = new Date();
  const todayStr = today.getFullYear() + '-' +
    String(today.getMonth() + 1).padStart(2, '0') + '-' +
    String(today.getDate()).padStart(2, '0');
  return props.date === todayStr;
});

const now = ref(new Date());
let timer = null;

onMounted(() => {
  timer = setInterval(() => { now.value = new Date(); }, 60000);
});
onUnmounted(() => { if (timer) clearInterval(timer); });

const currentTimePosition = computed(() => {
  const minutes = now.value.getHours() * 60 + now.value.getMinutes();
  const offset = minutes - START_HOUR * 60;
  if (offset < 0 || offset > totalMinutes) return -999;
  return (offset / totalMinutes) * hourWidth * hours.value.length;
});

const facilityColors = [
  'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500',
  'bg-rose-500', 'bg-cyan-500', 'bg-orange-500', 'bg-teal-500',
];

const facilityBgColors = [
  'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500',
  'bg-rose-500', 'bg-cyan-500', 'bg-orange-500', 'bg-teal-500',
];

function timeToMinutes(timeStr) {
  if (!timeStr) return 0;
  const parts = timeStr.split(':');
  return parseInt(parts[0]) * 60 + parseInt(parts[1]);
}

function eventStyle(event) {
  const startMin = Math.max(timeToMinutes(event.start), START_HOUR * 60);
  const endMin = Math.min(timeToMinutes(event.end), END_HOUR * 60);
  if (endMin <= startMin) return { display: 'none' };

  const gridWidth = hourWidth * hours.value.length;
  const left = ((startMin - START_HOUR * 60) / totalMinutes) * gridWidth;
  const width = ((endMin - startMin) / totalMinutes) * gridWidth;

  return {
    left: left + 'px',
    width: Math.max(width, 4) + 'px',
  };
}

const tooltip = ref({ visible: false, x: 0, y: 0, title: '', time: '', facility: '' });

function showTooltip(e, event, facilityName) {
  tooltip.value = {
    visible: true,
    x: e.clientX + 12,
    y: e.clientY - 60,
    title: event.title,
    time: `${event.start} - ${event.end}`,
    facility: facilityName,
  };
}

function hideTooltip() {
  tooltip.value.visible = false;
}

function changeDate(delta) {
  const d = new Date(props.date + 'T00:00:00');
  d.setDate(d.getDate() + delta);
  const newDate = d.getFullYear() + '-' +
    String(d.getMonth() + 1).padStart(2, '0') + '-' +
    String(d.getDate()).padStart(2, '0');
  router.get('/facility-timeline', { date: newDate }, { preserveState: true });
}

function goToday() {
  const today = new Date();
  const todayStr = today.getFullYear() + '-' +
    String(today.getMonth() + 1).padStart(2, '0') + '-' +
    String(today.getDate()).padStart(2, '0');
  router.get('/facility-timeline', { date: todayStr }, { preserveState: true });
}
</script>
