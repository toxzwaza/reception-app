<template>
  <ReceptionLayout
    title="集荷"
    subtitle="集荷する依頼を選択してください"
    :steps="['依頼選択', '伝票撮影', '完了']"
    :current-step="0"
  >
    <div class="mx-auto max-w-4xl">
      <div v-if="pickupRequests.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <Link
          v-for="req in pickupRequests"
          :key="req.id"
          :href="route('pickup.create', { request: req.id })"
          class="group block"
        >
          <div class="h-full rounded-2xl border border-white/70 bg-white/85 p-5 shadow-lg shadow-sky-900/5 backdrop-blur-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-xl">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="text-lg font-bold text-slate-800">{{ req.item }}</div>
                <div class="mt-1 text-sm text-slate-500">依頼者：{{ req.requester_name }}</div>
                <div v-if="req.storage_location" class="text-sm text-slate-500">置き場所：{{ req.storage_location }}</div>
                <div v-if="req.contact_phone" class="text-sm text-slate-500">問い合わせ：{{ req.contact_phone }}</div>
              </div>
              <svg class="mt-1 h-5 w-5 shrink-0 text-slate-300 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </div>
        </Link>
      </div>

      <div v-else class="rounded-2xl border border-slate-200 bg-white/85 p-10 text-center text-slate-500 backdrop-blur-sm">
        現在、登録済みの集荷依頼はありません。
      </div>

      <!-- 依頼なしで進む（飛び込みの集荷など） -->
      <div class="mt-6 text-center">
        <Link
          :href="route('pickup.create')"
          class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white/70 px-6 py-3 text-sm font-semibold text-slate-600 shadow-sm backdrop-blur transition hover:bg-white"
        >
          依頼にない集荷を行う（伝票のみ）→
        </Link>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';

defineProps({
  pickupRequests: { type: Array, default: () => [] },
});
</script>
