<template>
  <ReceptionLayout :show-back-button="false" :show-screensaver-button="true">
    <div class="flex min-h-[calc(100vh-13rem)] flex-col gap-6">
      <!-- ヒーロー見出し -->
      <div class="fade-up text-center">
        <p class="reception-script text-2xl text-sky-400/90 sm:text-3xl">Welcome to</p>
        <h1 class="mt-1 bg-gradient-to-r from-blue-700 via-sky-500 to-cyan-500 bg-clip-text text-5xl font-extrabold tracking-tight text-transparent drop-shadow-sm">
          AKIOKA Co., Ltd.
        </h1>
        <p class="mt-3 text-base font-medium text-slate-500">ご用件を選択してください</p>
      </div>

      <!-- 導線カード（画面パターンのレイアウトに従って12列グリッドで配置） -->
      <div
        v-if="tiles.length"
        class="reception-grid flex-1"
      >
        <Link
          v-for="(tile, index) in tiles"
          :key="tile.i"
          :href="route(tile.card.routeName)"
          class="fade-up group block"
          :style="{
            gridColumn: `${tile.x + 1} / span ${tile.w}`,
            gridRow: `${tile.y + 1} / span ${tile.h}`,
            animationDelay: `${120 + index * 80}ms`,
          }"
        >
          <div
            class="relative flex h-full flex-col overflow-hidden rounded-3xl border border-white/70 bg-white/85 p-6 shadow-lg shadow-sky-900/5 backdrop-blur-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl group-hover:shadow-sky-900/10"
          >
            <!-- 角の淡いグラデ装飾 -->
            <div
              :class="['pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br opacity-70 blur-2xl transition-transform duration-500 group-hover:scale-125', tile.card.blob]"
            ></div>

            <div class="relative flex items-start gap-4">
              <!-- アイコン -->
              <div :class="['flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl ring-1', tile.card.iconBg, tile.card.iconText, tile.card.ring]">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="tile.card.icon" />
                </svg>
              </div>
              <!-- テキスト -->
              <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-slate-800">{{ tile.card.title }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ tile.card.desc }}</p>
              </div>
              <!-- 右矢印 -->
              <svg class="mt-1 h-5 w-5 shrink-0 text-slate-300 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </div>

            <!-- タップして開く -->
            <div class="relative mt-auto pt-5">
              <span :class="['inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold text-white shadow-sm transition-colors', tile.card.btn]">
                タップして開く
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </span>
            </div>
          </div>
        </Link>
      </div>

      <!-- 導線が1つも無い場合 -->
      <div v-else class="fade-up flex flex-1 items-center justify-center">
        <p class="rounded-2xl border border-white/70 bg-white/70 px-8 py-6 text-center text-slate-500 shadow-md backdrop-blur-sm">
          この端末に表示する受付メニューが設定されていません。<br />管理者ボタンから画面パターンを選択してください。
        </p>
      </div>
    </div>

    <!-- 管理者ボタン＋モーダルは body 直下へ Teleport（フッター等の重なり順の影響を避ける） -->
    <Teleport to="body">
    <!-- 管理者ボタン（画面パターン切替）：左下に控えめに配置 -->
    <button
      type="button"
      @click="openAdmin"
      title="管理者：画面パターン切替"
      aria-label="管理者：画面パターン切替"
      class="fixed bottom-4 left-4 z-[80] flex h-11 w-11 items-center justify-center rounded-full border border-white/70 bg-white/60 text-slate-400 shadow-sm backdrop-blur-sm transition hover:bg-white hover:text-blue-600"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
    </button>

    <!-- 管理者モーダル -->
    <div v-if="adminOpen" class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm px-4" @click.self="closeAdmin">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <!-- パスワード入力 -->
        <template v-if="adminStep === 'password'">
          <h3 class="text-lg font-bold text-slate-800">管理者認証</h3>
          <p class="mt-1 text-sm text-slate-500">画面パターンを切り替えるにはパスワードを入力してください。</p>
          <form @submit.prevent="verifyPassword" class="mt-4 space-y-3">
            <input
              ref="pwInput"
              v-model="password"
              type="password"
              autocomplete="off"
              placeholder="画面切替パスワード"
              class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <div v-if="errorMsg" class="text-sm text-rose-600">{{ errorMsg }}</div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="closeAdmin" class="rounded-lg bg-slate-100 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 transition">キャンセル</button>
              <button type="submit" :disabled="loading || !password" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition">
                {{ loading ? '確認中…' : '認証' }}
              </button>
            </div>
          </form>
        </template>

        <!-- パターン選択 -->
        <template v-else>
          <h3 class="text-lg font-bold text-slate-800">画面パターンの選択</h3>
          <p class="mt-1 text-sm text-slate-500">この端末に表示する画面パターンを選んでください。選択内容はこの端末に記憶されます。</p>
          <div class="mt-4 max-h-80 space-y-2 overflow-y-auto">
            <!-- 全表示（既定） -->
            <button
              type="button"
              @click="applyPattern(null)"
              class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition"
              :class="selectedPatternId === null ? 'border-blue-400 bg-blue-50 ring-1 ring-blue-200' : 'border-slate-200 hover:bg-slate-50'"
            >
              <div>
                <div class="text-sm font-semibold text-slate-800">すべて表示（既定）</div>
                <div class="text-xs text-slate-500">全ての受付メニューを表示します。</div>
              </div>
              <span v-if="selectedPatternId === null" class="text-xs font-bold text-blue-600">選択中</span>
            </button>

            <button
              v-for="p in screenPatterns"
              :key="p.id"
              type="button"
              @click="applyPattern(p.id)"
              class="flex w-full items-center justify-between rounded-xl border px-4 py-3 text-left transition"
              :class="selectedPatternId === p.id ? 'border-blue-400 bg-blue-50 ring-1 ring-blue-200' : 'border-slate-200 hover:bg-slate-50'"
            >
              <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-800">{{ p.name }}</div>
                <div v-if="p.description" class="truncate text-xs text-slate-500">{{ p.description }}</div>
                <div class="mt-1 flex flex-wrap gap-1">
                  <span v-for="key in (p.features || [])" :key="key" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ featureLabel(key) }}</span>
                </div>
              </div>
              <span v-if="selectedPatternId === p.id" class="ml-2 shrink-0 text-xs font-bold text-blue-600">選択中</span>
            </button>

            <p v-if="!screenPatterns.length" class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
              登録済みの画面パターンがありません。管理画面で登録してください。
            </p>
          </div>
          <div class="mt-4 flex justify-end">
            <button type="button" @click="closeAdmin" class="rounded-lg bg-slate-100 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200 transition">閉じる</button>
          </div>
        </template>
      </div>
    </div>
    </Teleport>
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { Link } from "@inertiajs/vue3";
import ReceptionLayout from "@/Layouts/ReceptionLayout.vue";
import {
  FEATURE_CARDS,
  FEATURE_CARD_MAP,
  FEATURE_ORDER,
  defaultLayout,
  DEFAULT_W,
  DEFAULT_H,
} from "@/reception/features.js";

const props = defineProps({
  screenPatterns: { type: Array, default: () => [] },
});

const STORAGE_KEY = "reception_screen_pattern_id";

// ── 画面パターン（localStorage で端末ごとに選択） ──────────
const selectedPatternId = ref(null);

// 選択中パターン（無ければ null）
const activePattern = computed(() =>
  selectedPatternId.value === null
    ? null
    : props.screenPatterns.find((p) => p.id === selectedPatternId.value) || null
);

// 表示する導線キー（FEATURE_ORDER 順）。
// パターン未選択時は既定表示（defaultHidden の導線＝旧アポイントあり等は除外）。
const enabledKeys = computed(() => {
  if (!activePattern.value) {
    return FEATURE_ORDER.filter((k) => !FEATURE_CARD_MAP[k]?.defaultHidden);
  }
  const set = new Set(activePattern.value.features || []);
  return FEATURE_ORDER.filter((k) => set.has(k));
});

// 実際に描画するレイアウト（カスタムレイアウト優先、無い導線は末尾に既定配置）
const effectiveLayout = computed(() => {
  const keys = enabledKeys.value;
  const custom = activePattern.value?.layout;
  if (custom && custom.length) {
    const byKey = Object.fromEntries(custom.map((it) => [it.i, it]));
    const result = keys.filter((k) => byKey[k]).map((k) => ({ ...byKey[k] }));
    const missing = keys.filter((k) => !byKey[k]);
    if (missing.length) {
      const maxY = result.reduce((m, it) => Math.max(m, it.y + it.h), 0);
      missing.forEach((k, idx) => {
        result.push({
          i: k,
          x: (idx % 2) * DEFAULT_W,
          y: maxY + Math.floor(idx / 2) * DEFAULT_H,
          w: DEFAULT_W,
          h: DEFAULT_H,
        });
      });
    }
    return result;
  }
  return defaultLayout(keys);
});

// カード定義を付与したタイル
const tiles = computed(() =>
  effectiveLayout.value
    .filter((it) => FEATURE_CARD_MAP[it.i])
    .map((it) => ({ ...it, card: FEATURE_CARD_MAP[it.i] }))
);

const featureLabel = (key) => FEATURE_CARD_MAP[key]?.title || key;

onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY);
  if (saved !== null && saved !== "") {
    const id = Number(saved);
    // 保存済みパターンがまだ存在する場合のみ適用（削除済みなら全表示に戻す）
    if (props.screenPatterns.some((p) => p.id === id)) {
      selectedPatternId.value = id;
    } else {
      localStorage.removeItem(STORAGE_KEY);
    }
  }
});

// ── 管理者モーダル ──────────
const adminOpen = ref(false);
const adminStep = ref("password"); // 'password' | 'select'
const password = ref("");
const errorMsg = ref("");
const loading = ref(false);
const pwInput = ref(null);

const openAdmin = async () => {
  adminOpen.value = true;
  adminStep.value = "password";
  password.value = "";
  errorMsg.value = "";
  await nextTick();
  pwInput.value?.focus();
};

const closeAdmin = () => {
  adminOpen.value = false;
};

const verifyPassword = async () => {
  if (!password.value) return;
  loading.value = true;
  errorMsg.value = "";
  try {
    const { data } = await window.axios.post(route("screen-pattern.verify"), {
      password: password.value,
    });
    if (data.ok) {
      adminStep.value = "select";
    } else {
      errorMsg.value = data.message || "認証に失敗しました。";
    }
  } catch (e) {
    errorMsg.value = e.response?.data?.message || "パスワードが違います。";
  } finally {
    loading.value = false;
  }
};

const applyPattern = (id) => {
  selectedPatternId.value = id;
  if (id === null) {
    localStorage.removeItem(STORAGE_KEY);
  } else {
    localStorage.setItem(STORAGE_KEY, String(id));
  }
  closeAdmin();
};
</script>

<style scoped>
.reception-script {
  font-family: "Snell Roundhand", "Apple Chancery", "Segoe Script", "Brush Script MT", cursive;
  font-style: italic;
}

/* 12列グリッド（管理画面のレイアウトエディタと同じ列数・行高で WYSIWYG を揃える） */
.reception-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-auto-rows: 96px;
  gap: 20px;
}

/* 読み込み時のフェードアップ（順次出現） */
.fade-up {
  opacity: 0;
  animation: fadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@media (prefers-reduced-motion: reduce) {
  .fade-up {
    opacity: 1;
    animation: none;
  }
}
</style>
