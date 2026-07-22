<template>
  <div class="space-y-6">
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">パターン名 <span class="text-rose-500">*</span></label>
      <input v-model="form.name" type="text" required placeholder="例: 本社受付 / 工場入口" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
      <div v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">説明</label>
      <input v-model="form.description" type="text" placeholder="設置場所の補足など" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
      <div v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-2">表示する導線</label>
      <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
        <label
          v-for="opt in featureOptions"
          :key="opt.key"
          class="flex items-center gap-3 rounded-lg border px-4 py-3 cursor-pointer transition"
          :class="form.features.includes(opt.key) ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white hover:bg-slate-50'"
        >
          <input type="checkbox" :value="opt.key" v-model="form.features" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-sm font-medium text-slate-700">{{ opt.label }}</span>
        </label>
      </div>
      <p class="mt-1 text-xs text-slate-500">チェックした導線のみ受付トップに表示されます。</p>
      <div v-if="form.errors.features" class="mt-1 text-sm text-rose-600">{{ form.errors.features }}</div>
    </div>

    <!-- レイアウト配置エディタ -->
    <div>
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium text-slate-700">受付トップの配置（ドラッグで移動・右下でサイズ変更）</label>
        <button type="button" @click="resetLayout" class="text-xs font-semibold text-blue-600 hover:text-blue-800">配置を初期化</button>
      </div>
      <div ref="editorWrap" class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
        <div v-if="layoutModel.length === 0" class="py-10 text-center text-sm text-slate-400">
          表示する導線を選択すると、ここに配置カードが表示されます。
        </div>
        <GridLayout
          v-else
          v-model:layout="layoutModel"
          :col-num="12"
          :row-height="rowHeight"
          :margin="[margin, margin]"
          :is-draggable="true"
          :is-resizable="true"
          :vertical-compact="false"
          :prevent-collision="true"
          :use-css-transforms="true"
        >
          <GridItem
            v-for="item in layoutModel"
            :key="item.i"
            :x="item.x"
            :y="item.y"
            :w="item.w"
            :h="item.h"
            :i="item.i"
            :min-w="3"
            :min-h="1"
          >
            <div class="tile" :class="cardOf(item.i)?.iconBg">
              <div :class="['tile-icon', cardOf(item.i)?.iconText]">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="cardOf(item.i)?.icon" />
                </svg>
              </div>
              <span class="tile-title">{{ cardOf(item.i)?.title }}</span>
            </div>
          </GridItem>
        </GridLayout>
      </div>
      <p class="mt-1 text-xs text-slate-500">受付トップと同じ12列グリッドです。位置・大きさがそのまま受付画面に反映されます。</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">表示順</label>
        <input v-model.number="form.sort_order" type="number" min="0" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
        <p class="mt-1 text-xs text-slate-500">小さいほど選択リストの上に表示されます。</p>
        <div v-if="form.errors.sort_order" class="mt-1 text-sm text-rose-600">{{ form.errors.sort_order }}</div>
      </div>
      <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
          <span class="text-sm font-medium text-slate-700">有効（受付端末の選択肢に表示する）</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { GridLayout, GridItem } from 'grid-layout-plus';
import { FEATURE_CARD_MAP, DEFAULT_W, DEFAULT_H, defaultLayout } from '@/reception/features.js';

const props = defineProps({
  form: { type: Object, required: true },
  featureOptions: { type: Array, default: () => [] },
});

// エディタ表示用のグリッド設定（配置は列/行単位なので受付側と WYSIWYG が一致する）
const rowHeight = 64;
const margin = 14;

const cardOf = (key) => FEATURE_CARD_MAP[key];

// featureOptions の順で、有効な導線キーを返す
const enabledKeys = () => props.featureOptions.map((o) => o.key).filter((k) => (props.form.features || []).includes(k));

// 既存の配置を優先しつつ、対象キーを網羅したレイアウトを組み立てる
const buildLayout = (keys, base) => {
  const byKey = Object.fromEntries((base || []).map((it) => [it.i, it]));
  const kept = keys.filter((k) => byKey[k]).map((k) => ({ i: k, x: byKey[k].x, y: byKey[k].y, w: byKey[k].w, h: byKey[k].h }));
  const missing = keys.filter((k) => !byKey[k]);
  if (missing.length) {
    const maxY = kept.reduce((m, it) => Math.max(m, it.y + it.h), 0);
    missing.forEach((k, idx) => {
      kept.push({ i: k, x: (idx % 2) * DEFAULT_W, y: maxY + Math.floor(idx / 2) * DEFAULT_H, w: DEFAULT_W, h: DEFAULT_H });
    });
  }
  return kept;
};

// 作業用レイアウト（form.layout と同期）
const layoutModel = ref(buildLayout(enabledKeys(), props.form.layout));

// layoutModel の変更（ドラッグ/リサイズ含む）を form.layout に反映
watch(
  layoutModel,
  (val) => {
    props.form.layout = val.map((it) => ({ i: it.i, x: it.x, y: it.y, w: it.w, h: it.h }));
  },
  { deep: true, immediate: true }
);

// 導線チェックの増減に追従（既存の位置は保持）
watch(
  () => (props.form.features || []).slice(),
  () => {
    layoutModel.value = buildLayout(enabledKeys(), layoutModel.value);
  }
);

// 配置を2列並びの既定に戻す
const resetLayout = () => {
  layoutModel.value = defaultLayout(enabledKeys());
};
</script>

<style scoped>
.tile {
  display: flex;
  height: 100%;
  width: 100%;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.35);
  padding: 10px 12px;
  box-sizing: border-box;
  cursor: grab;
  user-select: none;
}
.tile:active {
  cursor: grabbing;
}
.tile-icon {
  display: inline-flex;
  height: 32px;
  width: 32px;
  align-items: center;
  justify-content: center;
  border-radius: 0.6rem;
  background: rgba(255, 255, 255, 0.75);
}
.tile-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: #334155;
  line-height: 1.2;
}

/* grid-layout-plus のリサイズハンドル / プレースホルダ（本パッケージはCSSを同梱しないため定義） */
:deep(.vgl-item) {
  touch-action: none;
  transition: none;
}
:deep(.vgl-item--placeholder) {
  background: #3b82f6;
  opacity: 0.15;
  border-radius: 1rem;
}
:deep(.vgl-item__resizer) {
  position: absolute;
  right: 3px;
  bottom: 3px;
  width: 18px;
  height: 18px;
  cursor: se-resize;
  z-index: 3;
}
:deep(.vgl-item__resizer)::after {
  content: "";
  position: absolute;
  right: 3px;
  bottom: 3px;
  width: 8px;
  height: 8px;
  border-right: 2px solid #64748b;
  border-bottom: 2px solid #64748b;
}
</style>
