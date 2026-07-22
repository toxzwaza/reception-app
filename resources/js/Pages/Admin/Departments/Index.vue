<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        部署電話番号管理
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 成功メッセージ -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl">
          {{ $page.props.flash.success }}
        </div>

        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <p class="max-w-2xl text-sm text-slate-600">
            受付端末の部署選択・内線発信で、選択された部署の電話番号へ受付端末から発信します。
            発信対象にするには電話番号を登録してください（未登録の部署は受付の部署選択に表示されません）。
            <br />
            <span class="text-slate-500">▲▼ で並べ替え、「表示順を保存」で受付画面・一覧の表示順に反映されます。</span>
          </p>
          <button
            @click="saveOrder"
            :disabled="!orderChanged || saving"
            class="shrink-0 inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {{ saving ? '保存中...' : '表示順を保存' }}
          </button>
        </div>

        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">表示順</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">部署名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">電話番号</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">所属人数</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="(department, index) in list" :key="department.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-1">
                      <button
                        @click="move(index, -1)"
                        :disabled="index === 0"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30"
                        title="上へ"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                      </button>
                      <button
                        @click="move(index, 1)"
                        :disabled="index === list.length - 1"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-30"
                        title="下へ"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                      </button>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm font-semibold text-slate-800">{{ department.name }}</td>
                  <td class="px-6 py-4 text-sm">
                    <Badge v-if="department.phone_number" variant="success" dot>
                      {{ department.phone_number }}
                    </Badge>
                    <Badge v-else variant="neutral">未登録</Badge>
                  </td>
                  <td class="px-6 py-4 text-sm text-center text-slate-600">{{ department.users_count }}</td>
                  <td class="px-6 py-4 text-sm text-right">
                    <Link :href="route('admin.departments.edit', department.id)" class="text-blue-600 hover:text-blue-800 font-medium">
                      編集
                    </Link>
                  </td>
                </tr>
                <tr v-if="list.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-slate-400">部署が登録されていません。</td>
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
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
  departments: { type: Array, default: () => [] },
});

// 並べ替え操作用のローカルコピー
const list = ref(props.departments.map((d) => ({ ...d })));
const saving = ref(false);

// 元の並び順から変更されたか
const orderChanged = computed(
  () => list.value.map((d) => d.id).join(',') !== props.departments.map((d) => d.id).join(',')
);

// index の項目を dir(-1:上 / +1:下) に移動
const move = (index, dir) => {
  const target = index + dir;
  if (target < 0 || target >= list.value.length) return;
  const arr = list.value;
  [arr[index], arr[target]] = [arr[target], arr[index]];
};

// 表示順を保存（並び順どおりの id 配列を送信）
const saveOrder = () => {
  saving.value = true;
  router.post(
    route('admin.departments.order'),
    { ids: list.value.map((d) => d.id) },
    {
      preserveScroll: true,
      onFinish: () => {
        saving.value = false;
      },
    }
  );
};
</script>
