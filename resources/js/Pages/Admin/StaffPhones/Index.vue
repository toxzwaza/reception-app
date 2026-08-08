<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        担当者呼出管理
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
          {{ $page.props.flash.success }}
        </div>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <p class="max-w-2xl text-sm text-slate-600">
            受付端末の「担当者検索呼出」で使用するヨミ（かな検索用）と個人携帯番号を管理します。
            携帯番号が登録されている担当者は、受付画面に「携帯」ボタンが表示されます。
          </p>
          <form @submit.prevent="search" class="flex shrink-0 items-center gap-2">
            <input
              v-model="searchKeyword"
              type="search"
              placeholder="名前・ヨミ・社員番号で検索"
              class="w-56 rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
            />
            <button
              type="submit"
              class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
              検索
            </button>
          </form>
        </div>

        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">社員番号</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">名前</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ヨミ</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">部署</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">携帯番号</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="member in staff" :key="member.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-3.5 text-sm tabular-nums text-slate-500">{{ member.emp_no }}</td>
                  <td class="px-6 py-3.5 text-sm font-semibold text-slate-800">{{ member.name }}</td>
                  <td class="px-6 py-3.5 text-sm text-slate-600">
                    <span v-if="member.name_kana">{{ member.name_kana }}</span>
                    <span v-else class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">未登録</span>
                  </td>
                  <td class="px-6 py-3.5 text-sm text-slate-600">{{ member.group?.name ?? '—' }}</td>
                  <td class="px-6 py-3.5 text-sm">
                    <span v-if="member.mobile_phone" class="tabular-nums text-slate-800">{{ member.mobile_phone }}</span>
                    <span v-else class="text-slate-400">—</span>
                  </td>
                  <td class="px-6 py-3.5 text-right">
                    <Link
                      :href="route('admin.staff-phones.edit', member.id)"
                      class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-blue-400 hover:text-blue-600"
                    >
                      編集
                    </Link>
                  </td>
                </tr>
                <tr v-if="staff.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400">該当する担当者がいません</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  staff: { type: Array, default: () => [] },
  keyword: { type: String, default: '' },
});

const searchKeyword = ref(props.keyword);

const search = () => {
  router.get(route('admin.staff-phones.index'), { keyword: searchKeyword.value }, { preserveState: true });
};
</script>
