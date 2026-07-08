<template>
  <div
    class="reception-shell relative flex min-h-screen flex-col overflow-hidden bg-sky-50 bg-cover bg-center bg-no-repeat"
    :style="{ backgroundImage: `url(${bgSrc})` }"
  >
    <!-- ヘッダー -->
    <header class="relative z-20 border-b border-white/60 bg-white/70 backdrop-blur-md">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
        <!-- ロゴ/タイトル -->
        <Link :href="route('home')" class="flex items-center gap-2.5 group">
          <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-sm shadow-blue-600/30 transition-transform group-hover:scale-105">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </span>
          <span class="text-lg font-bold tracking-tight text-slate-800">受付システム</span>
        </Link>

        <!-- 右側：スクリーンセーバー起動ボタン + 現在時刻 -->
        <div class="flex items-center gap-3">
          <button
            v-if="showScreensaverButton"
            type="button"
            @click="startSaver"
            title="スクリーンセーバーを起動"
            aria-label="スクリーンセーバーを起動"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-white/70 bg-white/60 text-slate-500 backdrop-blur-sm transition-colors hover:bg-white hover:text-blue-600"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 15.75V8.25l6 3.75-6 3.75z" />
            </svg>
          </button>

          <div class="flex items-center gap-2 rounded-full border border-white/70 bg-white/60 px-4 py-1.5 backdrop-blur-sm">
            <svg class="h-4 w-4 text-sky-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium tabular-nums text-slate-600">{{ currentTime }}</span>
          </div>
        </div>
      </div>
    </header>

    <!-- メインコンテンツ -->
    <main class="relative z-10 flex-1">
      <div class="page-enter mx-auto max-w-7xl px-6 py-5">
        <!-- ページタイトル（サブ画面用） -->
        <div v-if="title" class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-sky-500 bg-clip-text text-transparent">{{ title }}</h1>
              <div v-if="subtitle" class="mt-1 text-base font-medium text-slate-500">
                {{ subtitle }}
              </div>
            </div>
            <Link
              v-if="showBackButton"
              :href="route('home')"
              class="inline-flex items-center rounded-xl border border-white/70 bg-white/80 px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-sm backdrop-blur transition-all duration-200 hover:bg-white hover:shadow focus:outline-none focus:ring-2 focus:ring-sky-400"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              戻る
            </Link>
          </div>
        </div>

        <!-- ステップインジケーター -->
        <div v-if="steps && currentStep !== null" class="mb-6">
          <div class="flex items-center justify-center">
            <div class="flex w-full max-w-3xl items-center">
              <template v-for="(step, index) in steps" :key="index">
                <div class="flex items-center" :class="[index !== 0 && 'flex-1']">
                  <div v-if="index !== 0" class="h-1 w-full rounded-full" :class="[
                    index <= currentStep ? 'bg-gradient-to-r from-sky-500 to-blue-600' : 'bg-slate-200'
                  ]"></div>
                  <div
                    class="relative flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold shadow-md"
                    :class="[
                      index < currentStep ? 'bg-gradient-to-br from-sky-500 to-blue-600 text-white' :
                      index === currentStep ? 'bg-gradient-to-br from-blue-600 to-blue-700 text-white ring-4 ring-sky-200' :
                      'bg-slate-200 text-slate-600'
                    ]"
                  >
                    <span v-if="index < currentStep">
                      <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span v-else>{{ index + 1 }}</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
          <div class="mt-3 flex justify-center space-x-12">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="text-sm font-semibold"
              :class="[index <= currentStep ? 'text-blue-700' : 'text-slate-500']"
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
    <footer class="relative z-10">
      <div class="mx-auto max-w-7xl px-6 py-4 text-center text-xs font-medium text-slate-400">
        © {{ new Date().getFullYear() }} Reception System. All rights reserved.
      </div>
    </footer>

    <!-- スクリーンセーバー（無操作時のPR動画。タップで受付トップへ復帰） -->
    <transition
      enter-active-class="transition-opacity duration-500"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-500"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showSaver"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black"
        @click="dismissSaver"
        @touchstart="dismissSaver"
      >
        <video
          :src="videoSrc"
          class="h-full w-full object-cover"
          autoplay
          muted
          loop
          playsinline
        ></video>
        <div class="pointer-events-none absolute inset-x-0 bottom-12 text-center">
          <span class="inline-block animate-pulse rounded-full bg-white/15 px-6 py-2.5 text-lg font-medium text-white backdrop-blur-sm">
            画面をタップして受付を開始
          </span>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

// public 配下の背景素材（Vite の静的解決を避けるため実行時バインド）
const bgSrc = '/images/reception/background.png';

const props = defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  showBackButton: { type: Boolean, default: true },
  steps: { type: Array, default: () => [] },
  currentStep: { type: Number, default: null },
  // 受付トップ用：スクリーンセーバー手動起動ボタンを表示するか
  showScreensaverButton: { type: Boolean, default: false },
});

// ── スクリーンセーバー（無操作時のPR動画再生） ──────────
const videoSrc = '/videos/akioka_pr.mp4';
const IDLE_MS = 10 * 60 * 1000; // 10分間無操作で起動
const showSaver = ref(false);
let idleTimer = null;

// 無操作タイマーを開始/再開
const startIdleTimer = () => {
  clearTimeout(idleTimer);
  idleTimer = setTimeout(() => {
    showSaver.value = true;
  }, IDLE_MS);
};

// 操作を検知したらタイマーをリセット（セーバー表示中は無視）
const onActivity = () => {
  if (!showSaver.value) startIdleTimer();
};

// ボタンから即座にセーバーを起動
const startSaver = () => {
  clearTimeout(idleTimer);
  showSaver.value = true;
};

// セーバーを閉じて受付トップへ戻す
const dismissSaver = () => {
  showSaver.value = false;
  startIdleTimer();
  router.visit(route('home'));
};

const activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel', 'click'];

// 現在時刻の管理
const currentTime = ref(formatDateTime(new Date()));
let timeInterval;

function formatDateTime(date) {
  return new Intl.DateTimeFormat('ja-JP', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', weekday: 'short',
  }).format(date);
}

onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = formatDateTime(new Date());
  }, 1000);

  // 無操作検知の開始
  activityEvents.forEach((e) => window.addEventListener(e, onActivity, { passive: true }));
  startIdleTimer();
});

onUnmounted(() => {
  if (timeInterval) clearInterval(timeInterval);
  activityEvents.forEach((e) => window.removeEventListener(e, onActivity));
  clearTimeout(idleTimer);
});
</script>

<style scoped>
.reception-shell {
  min-height: 100vh;
}

/* ページ遷移時のエントランス（各画面がふわっと表示される） */
.page-enter {
  animation: pageEnter 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}
@keyframes pageEnter {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@media (prefers-reduced-motion: reduce) {
  .page-enter {
    animation: none;
  }
}
</style>
