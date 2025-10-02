<template>
  <ReceptionLayout title="納品・集荷の方" subtitle="用途を選択してください">
    <div class="p-8">
      <div class="max-w-2xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- 納品 -->
          <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-blue-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
            <Link :href="route('delivery.create')" class="block">
              <div class="flex flex-col items-center p-12 text-center group-hover:scale-105 transition-transform duration-200">
                <div class="w-20 h-20 mb-6 text-blue-500">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">納品</h2>
                <p class="text-gray-600">納品書・受領書の処理</p>
              </div>
            </Link>
          </div>

          <!-- 集荷 -->
          <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-orange-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
            <Link :href="route('pickup.create')" class="block">
              <div class="flex flex-col items-center p-12 text-center group-hover:scale-105 transition-transform duration-200">
                <div class="w-20 h-20 mb-6 text-orange-500">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">集荷</h2>
                <p class="text-gray-600">集荷伝票の処理</p>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ReceptionLayout from '@/Layouts/ReceptionLayout.vue';
</script>

