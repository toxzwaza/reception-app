<template>
  <ReceptionLayout
    title="受付システム"
    subtitle="ご用件を選択してください"
    :show-back-button="false"
  >
    <div class="px-4 py-4 h-[calc(100vh-140px)]">
      <div class="grid grid-cols-2 gap-4 h-full">
        <!-- アポイントありの方 -->
        <Link :href="route('appointment.index')" class="block">
          <div
            class="h-full bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 hover:border-blue-400 hover:shadow-xl transition-all duration-300 cursor-pointer group overflow-hidden relative"
          >
            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-125 transition-transform duration-500"></div>
            <div
              class="h-full flex flex-col items-center justify-center p-4 text-center relative z-10"
            >
              <div class="w-16 h-16 mb-4 text-blue-600 bg-white rounded-xl p-3 shadow-md group-hover:scale-105 group-hover:rotate-2 transition-all duration-300">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-blue-900 mb-2">
                アポイントありの方
              </h2>
              <p class="text-blue-700 text-sm font-medium mb-3">事前登録済みの方はこちら</p>
              <div class="px-4 py-1.5 bg-blue-600 text-white rounded-full text-xs font-bold group-hover:bg-blue-700 transition-colors">
                タップして進む →
              </div>
            </div>
          </div>
        </Link>

        <!-- アポイントなしの方 -->
        <Link :href="route('other-visitor.create')" class="block">
          <div
            class="h-full bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-xl border-2 border-cyan-200 hover:border-cyan-400 hover:shadow-xl transition-all duration-300 cursor-pointer group overflow-hidden relative"
          >
            <div class="absolute top-0 right-0 w-20 h-20 bg-cyan-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-125 transition-transform duration-500"></div>
            <div
              class="h-full flex flex-col items-center justify-center p-4 text-center relative z-10"
            >
              <div class="w-16 h-16 mb-4 text-cyan-600 bg-white rounded-xl p-3 shadow-md group-hover:scale-105 group-hover:rotate-2 transition-all duration-300">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                  />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-cyan-900 mb-2">
                アポイントなしの方
              </h2>
              <p class="text-cyan-700 text-sm font-medium mb-3">初めてお越しの方はこちら</p>
              <div class="px-4 py-1.5 bg-cyan-600 text-white rounded-full text-xs font-bold group-hover:bg-cyan-700 transition-colors">
                タップして進む →
              </div>
            </div>
          </div>
        </Link>

        <!-- 納品・集荷の方 -->
        <Link :href="route('delivery-pickup.select')" class="block">
          <div
            class="h-full bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl border-2 border-amber-200 hover:border-amber-400 hover:shadow-xl transition-all duration-300 cursor-pointer group overflow-hidden relative"
          >
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-125 transition-transform duration-500"></div>
            <div
              class="h-full flex flex-col items-center justify-center p-4 text-center relative z-10"
            >
              <div class="w-16 h-16 mb-4 text-amber-600 bg-white rounded-xl p-3 shadow-md group-hover:scale-105 group-hover:rotate-2 transition-all duration-300">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                  />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-amber-900 mb-2">
                納品・集荷の方
              </h2>
              <p class="text-amber-700 text-sm font-medium mb-3">納品書・集荷伝票の処理</p>
              <div class="px-4 py-1.5 bg-amber-600 text-white rounded-full text-xs font-bold group-hover:bg-amber-700 transition-colors">
                タップして進む →
              </div>
            </div>
          </div>
        </Link>

        <!-- 面接の方 -->
        <Link :href="route('interview.index')" class="block">
          <div
            class="h-full bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-xl border-2 border-emerald-200 hover:border-emerald-400 hover:shadow-xl transition-all duration-300 cursor-pointer group overflow-hidden relative"
          >
            <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-200 rounded-full -mr-10 -mt-10 opacity-20 group-hover:scale-125 transition-transform duration-500"></div>
            <div
              class="h-full flex flex-col items-center justify-center p-4 text-center relative z-10"
            >
              <div class="w-16 h-16 mb-4 text-emerald-600 bg-white rounded-xl p-3 shadow-md group-hover:scale-105 group-hover:rotate-2 transition-all duration-300">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
              <h2 class="text-xl font-bold text-emerald-900 mb-2">面接の方</h2>
              <p class="text-emerald-700 text-sm font-medium mb-3">面接にお越しの方はこちら</p>
              <div class="px-4 py-1.5 bg-emerald-600 text-white rounded-full text-xs font-bold group-hover:bg-emerald-700 transition-colors">
                タップして進む →
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </ReceptionLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import ReceptionLayout from "@/Layouts/ReceptionLayout.vue";
</script>