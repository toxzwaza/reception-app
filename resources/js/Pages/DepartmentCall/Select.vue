<template>
  <ReceptionLayout
    title="担当を呼ぶ"
    subtitle="部署を選択するか、担当者名で検索してください"
  >
    <div class="mx-auto max-w-7xl">
      <!-- エラー（電話番号未登録の部署が選ばれた等） -->
      <div v-if="error" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        {{ error }}
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <!-- 左：部署一覧 -->
        <section aria-label="部署から呼ぶ">
          <h2 class="mb-3 flex items-center gap-2 text-sm font-bold tracking-wide text-slate-600">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </span>
            部署から呼ぶ
          </h2>

          <div v-if="departments.length" class="grid grid-cols-2 gap-3">
            <Link
              v-for="dept in departments"
              :key="dept.id"
              :href="route('department-call.call', dept.id)"
              class="group block"
            >
              <div
                class="flex h-full items-center gap-3 rounded-2xl border border-white/70 bg-white/85 p-4 shadow-lg shadow-sky-900/5 backdrop-blur-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl"
              >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </span>
                <span class="min-w-0 flex-1 text-base font-bold text-slate-800">{{ dept.name }}</span>
                <svg class="h-4 w-4 shrink-0 text-slate-300 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </Link>
          </div>

          <div v-else class="rounded-2xl border border-slate-200 bg-white/85 p-8 text-center text-sm text-slate-500 backdrop-blur-sm">
            発信できる部署が登録されていません。<br />
            管理画面の「部署電話番号管理」で電話番号を登録してください。
          </div>
        </section>

        <!-- 右：担当者検索 -->
        <section aria-label="担当者名で探す">
          <h2 class="mb-3 flex items-center gap-2 text-sm font-bold tracking-wide text-slate-600">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
              <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
              </svg>
            </span>
            担当者名で探す
          </h2>

          <div class="rounded-2xl border border-white/70 bg-white/85 p-4 shadow-lg shadow-sky-900/5 backdrop-blur-sm">
            <!-- 検索入力（タップでフローティングキーボード表示） -->
            <div class="relative">
              <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
              </svg>
              <input
                :value="query"
                @input="query = $event.target.value"
                @focus="showKeyboard = true"
                type="text"
                inputmode="none"
                placeholder="ひらがなで名前を入力（例：あきおか）"
                aria-label="担当者名をひらがなで入力"
                class="w-full rounded-xl border-slate-300 bg-white py-3.5 pl-11 pr-11 text-lg focus:border-emerald-500 focus:ring-emerald-500"
              />
              <button
                v-if="query"
                type="button"
                @click="query = ''"
                aria-label="入力をクリア"
                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-slate-100 p-1.5 text-slate-500 transition hover:bg-slate-200"
              >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
              </button>
            </div>

            <!-- 検索結果 -->
            <div class="mt-3">
              <p v-if="!trimmedQuery" class="px-2 py-8 text-center text-sm text-slate-400">
                名前を入力すると担当者が表示されます
              </p>
              <p v-else-if="results.length === 0" class="px-2 py-8 text-center text-sm text-slate-400">
                「{{ query }}」に一致する担当者が見つかりません
              </p>
              <ul v-else class="max-h-[52vh] divide-y divide-slate-100 overflow-y-auto">
                <li
                  v-for="member in results"
                  :key="member.id"
                  class="flex items-center justify-between gap-3 px-2 py-3"
                >
                  <div class="min-w-0">
                    <p class="truncate text-base font-bold text-slate-800">{{ member.name }}</p>
                    <p class="mt-0.5 truncate text-xs text-slate-500">
                      {{ member.name_kana ?? '' }}
                      <span v-if="member.group_name" class="ml-1.5 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                        {{ member.group_name }}
                      </span>
                    </p>
                  </div>
                  <div class="flex shrink-0 items-center gap-2">
                    <!-- 携帯ボタン（個人携帯番号あり） -->
                    <Link
                      v-if="member.has_mobile"
                      :href="route('department-call.staff-call', { user: member.id, type: 'mobile' })"
                      class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 active:scale-95"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                      </svg>
                      携帯
                    </Link>
                    <!-- 部署ボタン（所属部署に電話番号あり） -->
                    <Link
                      v-if="member.group_has_phone"
                      :href="route('department-call.staff-call', { user: member.id, type: 'department' })"
                      class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 active:scale-95"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                      </svg>
                      部署
                    </Link>
                    <span v-if="!member.has_mobile && !member.group_has_phone" class="text-xs text-slate-400">
                      呼出先未登録
                    </span>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- フローティングひらがなキーボード -->
    <FloatingKanaKeyboard
      v-if="showKeyboard"
      v-model="query"
      @close="showKeyboard = false"
    />
  </ReceptionLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
import FloatingKanaKeyboard from '@/Components/FloatingKanaKeyboard.vue';

const props = defineProps({
  departments: { type: Array, default: () => [] },
  staff: { type: Array, default: () => [] },
  error: { type: String, default: '' },
});

const query = ref('');
const showKeyboard = ref(false);

const trimmedQuery = computed(() => query.value.trim());

// カタカナ→ひらがな変換＋空白除去（ヨミはカタカナ保存のため検索時に正規化して照合）
const toHiragana = (text) =>
  String(text ?? '')
    .replace(/[ァ-ヶ]/g, (ch) => String.fromCharCode(ch.charCodeAt(0) - 0x60))
    .replace(/[\s　]/g, '')
    .toLowerCase();

const results = computed(() => {
  const queryHira = toHiragana(trimmedQuery.value);
  if (!queryHira) return [];

  const matched = props.staff.filter((member) => {
    const kana = toHiragana(member.name_kana);
    const name = String(member.name ?? '').replace(/[\s　]/g, '');
    return (kana && kana.includes(queryHira)) || name.includes(trimmedQuery.value.replace(/[\s　]/g, ''));
  });

  // 前方一致を優先して表示
  return matched
    .sort((a, b) => {
      const aStarts = toHiragana(a.name_kana).startsWith(queryHira) ? 0 : 1;
      const bStarts = toHiragana(b.name_kana).startsWith(queryHira) ? 0 : 1;
      return aStarts - bStarts;
    })
    .slice(0, 30);
});
</script>
