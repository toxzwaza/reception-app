<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100">
    <!-- ヘッダー -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <!-- ロゴ/タイトル -->
          <Link :href="route('home')" class="flex items-center space-x-2">
            <span class="text-xl font-bold text-gray-900">受付システム</span>
          </Link>

          <!-- 現在時刻 -->
          <div class="text-gray-600">
            {{ currentTime }}
          </div>
        </div>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- ページタイトル -->
        <div v-if="title" class="mb-8">
          <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">{{ title }}</h1>
            <Link
              v-if="showBackButton"
              :href="route('home')"
              class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              戻る
            </Link>
          </div>
          <div v-if="subtitle" class="mt-2 text-sm text-gray-600">
            {{ subtitle }}
          </div>
        </div>

        <!-- ステップインジケーター -->
        <div v-if="steps && currentStep" class="mb-8">
          <div class="flex items-center justify-center">
            <div class="flex items-center w-full max-w-3xl">
              <template v-for="(step, index) in steps" :key="index">
                <div class="flex items-center" :class="[index !== 0 && 'flex-1']">
                  <div v-if="index !== 0" class="h-0.5 w-full" :class="[
                    index <= currentStep ? 'bg-indigo-600' : 'bg-gray-200'
                  ]"></div>
                  <div
                    class="relative flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium"
                    :class="[
                      index < currentStep ? 'bg-indigo-600 text-white' : 
                      index === currentStep ? 'bg-indigo-600 text-white' : 
                      'bg-gray-200 text-gray-700'
                    ]"
                  >
                    <span v-if="index < currentStep">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span v-else>{{ index + 1 }}</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
          <div class="flex justify-center mt-2 space-x-12">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="text-sm"
              :class="[
                index <= currentStep ? 'text-indigo-600 font-medium' : 'text-gray-500'
              ]"
            >
              {{ step }}
            </div>
          </div>
        </div>

        <!-- メインコンテンツ -->
        <Card>
          <slot></slot>
        </Card>
      </div>
    </main>

    <!-- フッター -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="text-center text-sm text-gray-500">
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
