<template>
  <ReceptionLayout
    title="担当部署を呼ぶ"
    subtitle="お呼びする部署を選択してください"
  >
    <div class="mx-auto max-w-4xl">
      <!-- エラー（電話番号未登録の部署が選ばれた等） -->
      <div v-if="error" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
        {{ error }}
      </div>

      <div v-if="departments.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3">
        <Link
          v-for="dept in departments"
          :key="dept.id"
          :href="route('department-call.call', dept.id)"
          class="group block"
        >
          <div
            class="flex h-full items-center gap-3 rounded-2xl border border-white/70 bg-white/85 p-5 shadow-lg shadow-sky-900/5 backdrop-blur-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl"
          >
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </span>
            <span class="min-w-0 flex-1 text-lg font-bold text-slate-800">{{ dept.name }}</span>
            <svg class="h-5 w-5 shrink-0 text-slate-300 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </div>
        </Link>
      </div>

      <div v-else class="rounded-2xl border border-slate-200 bg-white/85 p-10 text-center text-slate-500 backdrop-blur-sm">
        発信できる部署が登録されていません。<br />
        管理画面の「部署電話番号管理」で電話番号を登録してください。
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

defineProps({
  departments: { type: Array, default: () => [] },
  error: { type: String, default: '' },
});
</script>
