<template>
  <PageContainer title="受付システム">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- QRコード受付 -->
      <Card hoverable class="group">
        <Link :href="route('visitor.scan-qr')" class="block">
          <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
            <div class="w-16 h-16 mb-4 text-indigo-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">QRコード受付</h2>
            <p class="text-gray-600">事前登録済みの方はこちら</p>
          </div>
        </Link>
      </Card>

      <!-- QRコードなし受付 -->
      <Card hoverable class="group">
        <Link :href="route('visitor.create')" class="block">
          <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
            <div class="w-16 h-16 mb-4 text-green-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">QRコードなし受付</h2>
            <p class="text-gray-600">初めての方はこちら</p>
          </div>
        </Link>
      </Card>

      <!-- 納品業者受付 -->
      <Card hoverable class="group">
        <Link :href="route('delivery.create')" class="block">
          <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
            <div class="w-16 h-16 mb-4 text-yellow-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">納品業者受付</h2>
            <p class="text-gray-600">納品書・受領書の処理</p>
          </div>
        </Link>
      </Card>

      <!-- 集荷業者受付 -->
      <Card hoverable class="group">
        <Link :href="route('pickup.create')" class="block">
          <div class="flex flex-col items-center p-8 text-center group-hover:scale-105 transition-transform duration-200">
            <div class="w-16 h-16 mb-4 text-purple-500">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">集荷業者受付</h2>
            <p class="text-gray-600">集荷伝票の処理</p>
          </div>
        </Link>
      </Card>
    </div>
  </PageContainer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import PageContainer from '@/Components/UI/PageContainer.vue';
import Card from '@/Components/UI/Card.vue';

defineProps({
  title: String,
});
</script>