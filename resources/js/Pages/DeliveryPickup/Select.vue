<template>
  <ReceptionLayout 
    title="納品・集荷の方" 
    subtitle="納品・集荷を選択してください"
    :steps="['納品・集荷選択', '情報入力', '完了']"
    :current-step="0"
  >
    <div class="p-12">
      <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">納品・集荷を選択してください</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
        <!-- 納品 -->
        <div 
          class="bg-white rounded-xl border-2 border-gray-200 hover:border-indigo-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group p-12"
        >
          <Link :href="route('delivery.create')" class="block">
            <div class="flex flex-col items-center text-center">
              <div class="w-32 h-32 mb-6 text-indigo-500 group-hover:scale-110 transition-transform duration-200">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-3">納品</h3>
              <p class="text-gray-600">納品書・受領書の処理</p>
            </div>
          </Link>
        </div>

        <!-- 集荷 -->
        <div 
          class="bg-white rounded-xl border-2 border-gray-200 hover:border-green-500 hover:shadow-2xl transition-all duration-200 cursor-pointer group p-12"
        >
          <Link :href="route('pickup.create')" class="block">
            <div class="flex flex-col items-center text-center">
              <div class="w-32 h-32 mb-6 text-green-500 group-hover:scale-110 transition-transform duration-200">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
              </div>
              <h3 class="text-2xl font-bold text-gray-900 mb-3">集荷</h3>
              <p class="text-gray-600">集荷伝票の処理</p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
</script>

