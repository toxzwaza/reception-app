<template>
  <ReceptionLayout title="受付システム" subtitle="ご用件を選択してください" :show-back-button="false">
    <div class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- アポイントアリの方 -->
        <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-indigo-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
          <Link :href="route('appointment.index')" class="block">
            <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
              <div class="w-16 h-16 mb-4 text-indigo-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900 mb-2">アポイントアリの方</h2>
              <p class="text-gray-600">事前登録済みの方はこちら</p>
            </div>
          </Link>
        </div>

        <!-- 納品・集荷の方 -->
        <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-yellow-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
          <Link :href="route('delivery-pickup.select')" class="block">
            <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
              <div class="w-16 h-16 mb-4 text-yellow-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900 mb-2">納品・集荷の方</h2>
              <p class="text-gray-600">納品書・集荷伝票の処理</p>
            </div>
          </Link>
        </div>

        <!-- 面接の方 -->
        <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-green-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
          <Link :href="route('interview.index')" class="block">
            <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
              <div class="w-16 h-16 mb-4 text-green-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900 mb-2">面接の方</h2>
              <p class="text-gray-600">面接にお越しの方はこちら</p>
            </div>
          </Link>
        </div>

        <!-- その他の方 -->
        <div class="bg-white rounded-lg border-2 border-gray-200 hover:border-purple-500 hover:shadow-lg transition-all duration-200 cursor-pointer group">
          <Link :href="route('other-visitor.create')" class="block">
            <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
              <div class="w-16 h-16 mb-4 text-purple-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <h2 class="text-xl font-semibold text-gray-900 mb-2">その他の方</h2>
              <p class="text-gray-600">初めてお越しの方はこちら</p>
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