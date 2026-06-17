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

        <p class="mb-4 text-sm text-slate-600">
          アポイントなしの来訪受付時、選択された部署の電話番号へ受付端末から自動で発信します。
          発信対象にするには電話番号を登録してください（未登録の部署は受付の部署選択に表示されません）。
        </p>

        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">部署名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">電話番号</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">所属人数</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="department in departments" :key="department.id" class="hover:bg-blue-50/50 transition-colors">
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
                <tr v-if="departments.length === 0">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-400">部署が登録されていません。</td>
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
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

defineProps({
  departments: { type: Array, default: () => [] },
});
</script>
