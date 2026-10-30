<template>
  <div class="space-y-6">
    <!-- 端末番号 -->
    <div>
      <label for="number" class="block text-sm font-medium text-slate-700 mb-1">
        端末番号 <span class="text-rose-500">*</span>
      </label>
      <input
        id="number"
        v-model.number="form.number"
        type="number"
        min="1"
        max="99"
        required
        class="w-32 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      />
      <p class="text-xs text-slate-500 mt-1">1〜99。タブレットで <code>/?terminal=番号</code> を開いて設定します。</p>
      <div v-if="form.errors.number" class="text-sm text-rose-600 mt-1">{{ form.errors.number }}</div>
    </div>

    <!-- 設置場所名 -->
    <div>
      <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
        設置場所名 <span class="text-rose-500">*</span>
      </label>
      <input
        id="name"
        v-model="form.name"
        type="text"
        maxlength="100"
        required
        placeholder="新社屋 来客玄関"
        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      />
      <div v-if="form.errors.name" class="text-sm text-rose-600 mt-1">{{ form.errors.name }}</div>
    </div>

    <!-- カメラ -->
    <div>
      <label for="camera_src" class="block text-sm font-medium text-slate-700 mb-1">
        全画面にするカメラ <span class="text-rose-500">*</span>
      </label>
      <select
        id="camera_src"
        v-model="form.camera_src"
        required
        class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      >
        <option value="" disabled>― カメラを選択 ―</option>
        <option v-for="camera in cameras" :key="camera.src" :value="camera.src">
          {{ camera.label }}（{{ camera.src }}）
        </option>
      </select>
      <div v-if="form.errors.camera_src" class="text-sm text-rose-600 mt-1">{{ form.errors.camera_src }}</div>
    </div>

    <!-- 有効/無効 -->
    <div>
      <label class="inline-flex items-center gap-2">
        <input
          v-model="form.enabled"
          type="checkbox"
          class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500"
        />
        <span class="text-sm font-medium text-slate-700">有効</span>
      </label>
      <p class="text-xs text-slate-500 mt-1">無効にすると、この端末からの受付ではチャイム・全画面表示を行いません（Teams通知はこれまでどおり）。</p>
      <div v-if="form.errors.enabled" class="text-sm text-rose-600 mt-1">{{ form.errors.enabled }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: { type: Object, required: true },
  cameras: { type: Array, default: () => [] },
});
</script>
