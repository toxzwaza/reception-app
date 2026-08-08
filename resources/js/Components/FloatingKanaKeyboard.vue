<script setup>
/**
 * 受付タブレット用フローティングひらがなキーボード。
 * - 50音配列＋濁点/半濁点/小文字変換・長音・削除・全消去
 * - ヘッダーのタップ＆ドラッグで画面内を自由に移動できる（画面外へのはみ出しは自動補正）
 * - サイズは画面の約1/4を目安（幅約48vw）
 */
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'close']);

// 50音（列ごと・あ行〜わ行）
const kanaColumns = [
    ['あ', 'い', 'う', 'え', 'お'],
    ['か', 'き', 'く', 'け', 'こ'],
    ['さ', 'し', 'す', 'せ', 'そ'],
    ['た', 'ち', 'つ', 'て', 'と'],
    ['な', 'に', 'ぬ', 'ね', 'の'],
    ['は', 'ひ', 'ふ', 'へ', 'ほ'],
    ['ま', 'み', 'む', 'め', 'も'],
    ['や', '', 'ゆ', '', 'よ'],
    ['ら', 'り', 'る', 'れ', 'ろ'],
    ['わ', '', 'を', '', 'ん'],
];

// 末尾文字の変換テーブル
const DAKUTEN = {
    か: 'が', き: 'ぎ', く: 'ぐ', け: 'げ', こ: 'ご',
    さ: 'ざ', し: 'じ', す: 'ず', せ: 'ぜ', そ: 'ぞ',
    た: 'だ', ち: 'ぢ', つ: 'づ', て: 'で', と: 'ど',
    は: 'ば', ひ: 'び', ふ: 'ぶ', へ: 'べ', ほ: 'ぼ',
    // 再タップで元に戻す
    が: 'か', ぎ: 'き', ぐ: 'く', げ: 'け', ご: 'こ',
    ざ: 'さ', じ: 'し', ず: 'す', ぜ: 'せ', ぞ: 'そ',
    だ: 'た', ぢ: 'ち', づ: 'つ', で: 'て', ど: 'と',
    ば: 'は', び: 'ひ', ぶ: 'ふ', べ: 'へ', ぼ: 'ほ',
    ぱ: 'ば', ぴ: 'び', ぷ: 'ぶ', ぺ: 'べ', ぽ: 'ぼ',
};
const HANDAKUTEN = {
    は: 'ぱ', ひ: 'ぴ', ふ: 'ぷ', へ: 'ぺ', ほ: 'ぽ',
    ば: 'ぱ', び: 'ぴ', ぶ: 'ぷ', べ: 'ぺ', ぼ: 'ぽ',
    ぱ: 'は', ぴ: 'ひ', ぷ: 'ふ', ぺ: 'へ', ぽ: 'ほ',
};
const SMALL = {
    あ: 'ぁ', い: 'ぃ', う: 'ぅ', え: 'ぇ', お: 'ぉ',
    や: 'ゃ', ゆ: 'ゅ', よ: 'ょ', つ: 'っ', わ: 'ゎ',
    ぁ: 'あ', ぃ: 'い', ぅ: 'う', ぇ: 'え', ぉ: 'お',
    ゃ: 'や', ゅ: 'ゆ', ょ: 'よ', っ: 'つ', ゎ: 'わ',
};

const append = (char) => {
    if (!char) return;
    emit('update:modelValue', props.modelValue + char);
};

const transformLast = (table) => {
    const value = props.modelValue;
    if (!value) return;
    const last = value.slice(-1);
    const converted = table[last];
    if (converted) emit('update:modelValue', value.slice(0, -1) + converted);
};

const backspace = () => {
    if (props.modelValue) emit('update:modelValue', props.modelValue.slice(0, -1));
};

const clearAll = () => emit('update:modelValue', '');

// --- ドラッグ移動（タップ＆ドラッグ / 画面外へのはみ出し防止） ---
const keyboardRef = ref(null);
const position = ref({ x: null, y: null });
let dragging = false;
let dragOffset = { x: 0, y: 0 };

const clampToViewport = (x, y) => {
    const el = keyboardRef.value;
    if (!el) return { x, y };
    const rect = el.getBoundingClientRect();
    return {
        x: Math.min(Math.max(x, 8), window.innerWidth - rect.width - 8),
        y: Math.min(Math.max(y, 8), window.innerHeight - rect.height - 8),
    };
};

const onDragStart = (event) => {
    const el = keyboardRef.value;
    if (!el) return;
    dragging = true;
    const rect = el.getBoundingClientRect();
    dragOffset = { x: event.clientX - rect.left, y: event.clientY - rect.top };
    window.addEventListener('pointermove', onDragMove);
    window.addEventListener('pointerup', onDragEnd);
};

const onDragMove = (event) => {
    if (!dragging) return;
    event.preventDefault();
    position.value = clampToViewport(event.clientX - dragOffset.x, event.clientY - dragOffset.y);
};

const onDragEnd = () => {
    dragging = false;
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
};

// 初期位置：右下（画面の1/4サイズを想定して配置）
onMounted(() => {
    const el = keyboardRef.value;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    position.value = {
        x: window.innerWidth - rect.width - 24,
        y: window.innerHeight - rect.height - 24,
    };
});

onUnmounted(() => {
    window.removeEventListener('pointermove', onDragMove);
    window.removeEventListener('pointerup', onDragEnd);
});
</script>

<template>
    <Teleport to="body">
        <div
            ref="keyboardRef"
            class="fixed z-50 w-[92vw] max-w-[560px] select-none rounded-2xl border border-slate-300 bg-white/95 shadow-2xl shadow-slate-900/25 backdrop-blur sm:w-[48vw]"
            :style="position.x !== null ? { left: `${position.x}px`, top: `${position.y}px` } : { right: '24px', bottom: '24px' }"
            role="application"
            aria-label="ひらがな入力キーボード"
        >
            <!-- ドラッグハンドル -->
            <div
                class="flex cursor-move items-center justify-between rounded-t-2xl border-b border-slate-200 bg-slate-100 px-4 py-2 touch-none"
                @pointerdown="onDragStart"
            >
                <div class="flex items-center gap-2 text-slate-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M7 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm0 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm-1 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9-13a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm-1 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm1 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" />
                    </svg>
                    <span class="text-xs font-semibold text-slate-500">ひらがな入力（ドラッグで移動できます）</span>
                </div>
                <button
                    type="button"
                    @pointerdown.stop
                    @click="emit('close')"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600"
                    aria-label="キーボードを閉じる"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>

            <!-- 50音キー -->
            <div class="p-3">
                <div class="grid grid-cols-10 gap-1.5">
                    <div v-for="(column, colIndex) in kanaColumns" :key="colIndex" class="flex flex-col gap-1.5">
                        <template v-for="(kana, rowIndex) in column" :key="`${colIndex}-${rowIndex}`">
                            <button
                                v-if="kana"
                                type="button"
                                @click="append(kana)"
                                class="rounded-lg border border-slate-200 bg-white py-2 text-base font-bold text-slate-700 shadow-sm transition hover:bg-blue-50 hover:text-blue-700 active:scale-95 active:bg-blue-100 sm:py-2.5"
                            >
                                {{ kana }}
                            </button>
                            <span v-else class="py-2 sm:py-2.5" aria-hidden="true"></span>
                        </template>
                    </div>
                </div>

                <!-- 操作キー -->
                <div class="mt-2 grid grid-cols-6 gap-1.5">
                    <button
                        type="button"
                        @click="transformLast(DAKUTEN)"
                        class="rounded-lg border border-slate-200 bg-slate-50 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-blue-50 active:scale-95"
                    >
                        ゛濁点
                    </button>
                    <button
                        type="button"
                        @click="transformLast(HANDAKUTEN)"
                        class="rounded-lg border border-slate-200 bg-slate-50 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-blue-50 active:scale-95"
                    >
                        ゜半濁点
                    </button>
                    <button
                        type="button"
                        @click="transformLast(SMALL)"
                        class="rounded-lg border border-slate-200 bg-slate-50 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-blue-50 active:scale-95"
                    >
                        小文字
                    </button>
                    <button
                        type="button"
                        @click="append('ー')"
                        class="rounded-lg border border-slate-200 bg-slate-50 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-blue-50 active:scale-95"
                    >
                        ー 長音
                    </button>
                    <button
                        type="button"
                        @click="backspace"
                        class="rounded-lg border border-amber-200 bg-amber-50 py-2 text-sm font-bold text-amber-700 shadow-sm transition hover:bg-amber-100 active:scale-95"
                    >
                        ⌫ 1文字
                    </button>
                    <button
                        type="button"
                        @click="clearAll"
                        class="rounded-lg border border-red-200 bg-red-50 py-2 text-sm font-bold text-red-600 shadow-sm transition hover:bg-red-100 active:scale-95"
                    >
                        全消去
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
