<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
    <!-- ヘッダー -->
    <header class="bg-gradient-to-r from-blue-600 to-blue-700 shadow-lg">
      <div class="max-w-7xl mx-auto px-6 py-3">
        <div class="flex items-center justify-between">
          <!-- ロゴ/タイトル -->
          <Link :href="route('home')" class="flex items-center space-x-3 group">
            <div class="bg-white p-2 rounded-lg shadow-md group-hover:shadow-xl transition-shadow">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <span class="text-2xl font-bold text-white tracking-tight">受付システム</span>
          </Link>

          <!-- 現在時刻 -->
          <div class="flex items-center gap-2 bg-blue-800 bg-opacity-30 px-4 py-2 rounded-lg backdrop-blur-sm">
            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-white font-medium">{{ currentTime }}</span>
          </div>
        </div>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="py-6">
      <div class="max-w-7xl mx-auto px-6">
        <!-- ページタイトル -->
        <div v-if="title" class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">{{ title }}</h1>
              <div v-if="subtitle" class="mt-1 text-base text-gray-600 font-medium">
                {{ subtitle }}
              </div>
            </div>
            <Link
              v-if="showBackButton"
              :href="route('home')"
              class="inline-flex items-center px-5 py-2.5 bg-white border-2 border-blue-200 rounded-xl font-semibold text-sm text-blue-700 hover:bg-blue-50 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-md"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              戻る
            </Link>
          </div>
        </div>

        <!-- ステップインジケーター -->
        <div v-if="steps && currentStep !== null" class="mb-6">
          <div class="flex items-center justify-center">
            <div class="flex items-center w-full max-w-3xl">
              <template v-for="(step, index) in steps" :key="index">
                <div class="flex items-center" :class="[index !== 0 && 'flex-1']">
                  <div v-if="index !== 0" class="h-1 w-full rounded-full" :class="[
                    index <= currentStep ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 'bg-gray-200'
                  ]"></div>
                  <div
                    class="relative flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold shadow-md"
                    :class="[
                      index < currentStep ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white' : 
                      index === currentStep ? 'bg-gradient-to-br from-blue-600 to-blue-700 text-white ring-4 ring-blue-200' : 
                      'bg-gray-200 text-gray-600'
                    ]"
                  >
                    <span v-if="index < currentStep">
                      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span v-else>{{ index + 1 }}</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
          <div class="flex justify-center mt-3 space-x-12">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="text-sm font-semibold"
              :class="[
                index <= currentStep ? 'text-blue-700' : 'text-gray-500'
              ]"
            >
              {{ step }}
            </div>
          </div>
        </div>

        <!-- メインコンテンツ -->
        <slot></slot>
      </div>
    </main>

    <!-- フッター -->
    <footer class="bg-gradient-to-r from-blue-600 to-blue-700 border-t-4 border-blue-800 mt-auto">
      <div class="max-w-7xl mx-auto px-6 py-3">
        <div class="text-center text-sm text-blue-100 font-medium">
          © {{ new Date().getFullYear() }} Reception System. All rights reserved.
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  showBackButton: {
    type: Boolean,
    default: true
  },
  steps: {
    type: Array,
    default: () => []
  },
  currentStep: {
    type: Number,
    default: null
  }
});

// 現在時刻の管理
const currentTime = ref(formatDateTime(new Date()));
let timeInterval;

// 時刻のフォーマット
function formatDateTime(date) {
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    weekday: 'short'
  }).format(date);
}

// 時刻の更新
onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = formatDateTime(new Date());
  }, 1000);
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
</script>

<style scoped>
.min-h-screen {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
</style>
