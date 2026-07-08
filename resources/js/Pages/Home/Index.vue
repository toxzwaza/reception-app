<template>
  <ReceptionLayout :show-back-button="false" :show-screensaver-button="true">
    <div class="flex min-h-[calc(100vh-13rem)] flex-col gap-6">
      <!-- ヒーロー見出し -->
      <div class="fade-up text-center">
        <p class="reception-script text-3xl text-sky-400 leading-none">Welcome to AKIOKA Co., Ltd.</p>
        <h1 class="mt-1 text-4xl font-bold tracking-tight bg-gradient-to-r from-blue-600 to-sky-500 bg-clip-text text-transparent">
          受付システム
        </h1>
        <p class="mt-2 text-base font-medium text-slate-500">ご用件を選択してください</p>
      </div>

      <!-- 用件カード 2×2 -->
      <div class="grid flex-1 grid-cols-2 gap-5">
        <Link
          v-for="(item, index) in menuItems"
          :key="item.title"
          :href="item.href"
          class="fade-up group block h-full"
          :style="{ animationDelay: `${120 + index * 90}ms` }"
        >
          <div
            class="relative flex h-full flex-col overflow-hidden rounded-3xl border border-white/70 bg-white/85 p-6 shadow-lg shadow-sky-900/5 backdrop-blur-sm transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-2xl group-hover:shadow-sky-900/10"
          >
            <!-- 角の淡いグラデ装飾 -->
            <div
              :class="['pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-gradient-to-br opacity-70 blur-2xl transition-transform duration-500 group-hover:scale-125', item.blob]"
            ></div>

            <div class="relative flex items-start gap-4">
              <!-- アイコン -->
              <div :class="['flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl ring-1', item.iconBg, item.iconText, item.ring]">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                </svg>
              </div>
              <!-- テキスト -->
              <div class="min-w-0 flex-1">
                <h2 class="text-lg font-bold text-slate-800">{{ item.title }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ item.desc }}</p>
              </div>
              <!-- 右矢印 -->
              <svg class="mt-1 h-5 w-5 shrink-0 text-slate-300 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </div>

            <!-- タップして開く -->
            <div class="relative mt-auto pt-5">
              <span :class="['inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold text-white shadow-sm transition-colors', item.btn]">
                タップして開く
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </span>
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

const menuItems = [
  {
    title: "アポイントありの方",
    desc: "事前登録済みの方はこちら",
    href: route("appointment.index"),
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    iconBg: "bg-blue-50",
    iconText: "text-blue-600",
    ring: "ring-blue-100",
    blob: "from-blue-200/70 to-transparent",
    btn: "bg-blue-600 group-hover:bg-blue-700",
  },
  {
    title: "アポイントなしの方",
    desc: "初めてお越しの方はこちら",
    href: route("other-visitor.create"),
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
    iconBg: "bg-sky-50",
    iconText: "text-sky-600",
    ring: "ring-sky-100",
    blob: "from-sky-200/70 to-transparent",
    btn: "bg-sky-600 group-hover:bg-sky-700",
  },
  {
    title: "納品・集荷の方",
    desc: "納品書・集荷伝票の処理",
    href: route("delivery-pickup.select"),
    icon: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4",
    iconBg: "bg-amber-50",
    iconText: "text-amber-600",
    ring: "ring-amber-100",
    blob: "from-amber-200/70 to-transparent",
    btn: "bg-amber-500 group-hover:bg-amber-600",
  },
  {
    title: "面接の方",
    desc: "面接にお越しの方はこちら",
    href: route("interview.index"),
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    iconBg: "bg-emerald-50",
    iconText: "text-emerald-600",
    ring: "ring-emerald-100",
    blob: "from-emerald-200/70 to-transparent",
    btn: "bg-emerald-600 group-hover:bg-emerald-700",
  },
];
</script>

<style scoped>
.reception-script {
  font-family: "Snell Roundhand", "Apple Chancery", "Segoe Script", "Brush Script MT", cursive;
  font-style: italic;
}

/* 読み込み時のフェードアップ（順次出現） */
.fade-up {
  opacity: 0;
  animation: fadeUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}
@keyframes fadeUp {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
@media (prefers-reduced-motion: reduce) {
  .fade-up {
    opacity: 1;
    animation: none;
  }
}
</style>
