<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">管理画面ダッシュボード</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- お知らせセクション -->
        <div class="mb-8" v-if="announcements.length > 0">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">お知らせ</h3>
          <div class="space-y-4">
            <div 
              v-for="announcement in announcements" 
              :key="announcement.id"
              :class="[
                'p-4 rounded-lg border-l-4',
                announcement.type === 'error' ? 'bg-red-50 border-red-500' : 
                announcement.type === 'warning' ? 'bg-yellow-50 border-yellow-500' : 
                'bg-blue-50 border-blue-500'
              ]"
            >
              <h4 class="font-semibold mb-2">{{ announcement.title }}</h4>
              <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ announcement.content }}</p>
              <p class="text-xs text-gray-500 mt-2">
                表示期間: {{ announcement.start_date }} 〜 {{ announcement.end_date }}
              </p>
            </div>
          </div>
        </div>

        <!-- 統計情報 -->
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">統計情報</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <div class="text-sm font-medium text-gray-500">今日のアポイント</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.todayAppointments }}</div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <div class="text-sm font-medium text-gray-500">未チェックイン</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.pendingAppointments }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 機能メニュー -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">機能メニュー</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- 事前アポイント登録 -->
            <Link 
              :href="route('admin.appointments.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-indigo-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">事前アポイント登録</h4>
                <p class="text-sm text-gray-600">訪問者の事前登録とアポイント管理</p>
              </div>
            </Link>

            <!-- 施設予約管理 -->
            <Link 
              :href="route('admin.facility-reservations.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-purple-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">施設予約管理</h4>
                <p class="text-sm text-gray-600">会議室など施設の予約管理</p>
              </div>
            </Link>

            <!-- スタッフメンバー管理 -->
            <Link 
              :href="route('admin.staff-members.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-green-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">スタッフメンバー管理</h4>
                <p class="text-sm text-gray-600">スタッフメンバーの登録と管理</p>
              </div>
            </Link>

            <!-- 通知設定管理 -->
            <Link 
              :href="route('admin.notification-settings.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-orange-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-orange-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5z" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">通知設定管理</h4>
                <p class="text-sm text-gray-600">各種通知の設定と管理</p>
              </div>
            </Link>

            <!-- お知らせ管理 -->
            <Link 
              :href="route('admin.announcements.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-yellow-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">お知らせ管理</h4>
                <p class="text-sm text-gray-600">ダッシュボードに表示するお知らせの管理</p>
              </div>
            </Link>

            <!-- 納品書・受領書管理 -->
            <Link 
              :href="route('admin.deliveries.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-blue-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">納品書・受領書管理</h4>
                <p class="text-sm text-gray-600">納品書・受領書の確認と電子印管理</p>
              </div>
            </Link>

            <!-- 集荷伝票管理 -->
            <Link 
              :href="route('admin.pickups.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-purple-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">集荷伝票管理</h4>
                <p class="text-sm text-gray-600">集荷伝票の確認と電子印管理</p>
              </div>
            </Link>

            <!-- プロジェクトグループ管理 -->
            <Link 
              :href="route('admin.project-groups.index')"
              class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border-2 border-transparent hover:border-teal-500"
            >
              <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 bg-teal-100 rounded-lg mb-4">
                  <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">プロジェクトグループ管理</h4>
                <p class="text-sm text-gray-600">プロジェクトチームの登録と管理</p>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
  announcements: Array,
  stats: Object,
});
</script>



