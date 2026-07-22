<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          画面パターン管理
        </h2>
        <Link
          :href="route('admin.screen-patterns.create')"
          class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition"
        >
          ＋ 新規登録
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div v-if="$page.props.flash?.success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
          {{ $page.props.flash.success }}
        </div>

        <!-- 説明 -->
        <div class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
          受付端末の設置場所ごとに、受付トップに表示する導線の組み合わせを「画面パターン」として登録できます。
          受付端末側は右上の管理者ボタン（歯車）から画面切替パスワードを入力してパターンを選択します。選択結果は端末に記憶されます。
        </div>

        <!-- 画面切替パスワード設定 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-slate-800">画面切替パスワード</h3>
              <p class="mt-0.5 text-sm text-slate-500">
                受付端末でパターンを切り替える際に入力するパスワードです。
                <span :class="passwordIsSet ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold'">
                  {{ passwordIsSet ? '設定済み' : '未設定' }}
                </span>
              </p>
            </div>
          </div>
          <form @submit.prevent="submitPassword" class="mt-4 flex flex-wrap items-end gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">新しいパスワード</label>
              <input v-model="passwordForm.password" type="password" autocomplete="new-password" placeholder="4文字以上" class="w-64 rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="passwordForm.errors.password" class="mt-1 text-sm text-rose-600">{{ passwordForm.errors.password }}</div>
            </div>
            <button type="submit" :disabled="passwordForm.processing" class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2 rounded-lg text-sm font-semibold disabled:opacity-50 transition">
              {{ passwordIsSet ? '変更する' : '設定する' }}
            </button>
          </form>
        </div>

        <!-- パターン一覧 -->
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">パターン名</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">表示する導線</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">状態</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">操作</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100">
                <tr v-for="p in patterns" :key="p.id" class="hover:bg-blue-50/50 transition-colors">
                  <td class="px-6 py-4 align-top">
                    <div class="text-sm font-semibold text-slate-800">{{ p.name }}</div>
                    <div v-if="p.description" class="mt-0.5 text-xs text-slate-500">{{ p.description }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1.5">
                      <span
                        v-for="key in (p.features || [])"
                        :key="key"
                        class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-100"
                      >
                        {{ featureLabel(key) }}
                      </span>
                      <span v-if="!p.features || p.features.length === 0" class="text-xs text-slate-400">（表示なし）</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', p.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'bg-slate-100 text-slate-500']">
                      {{ p.is_active ? '有効' : '無効' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                    <div class="flex justify-end gap-3">
                      <Link :href="route('admin.screen-patterns.edit', p.id)" class="text-blue-600 hover:text-blue-800">編集</Link>
                      <button @click="destroy(p)" class="text-rose-600 hover:text-rose-800">削除</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="patterns.length === 0">
                  <td colspan="4" class="px-6 py-10 text-center text-slate-400">画面パターンが登録されていません。</td>
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
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  patterns: { type: Array, default: () => [] },
  featureOptions: { type: Array, default: () => [] },
  passwordIsSet: { type: Boolean, default: false },
});

const featureLabel = (key) => props.featureOptions.find((f) => f.key === key)?.label || key;

const passwordForm = useForm({ password: '' });
const submitPassword = () => {
  passwordForm.post(route('admin.screen-patterns.password'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset('password'),
  });
};

const destroy = (p) => {
  if (confirm(`画面パターン「${p.name}」を削除しますか？`)) {
    router.delete(route('admin.screen-patterns.destroy', p.id), { preserveScroll: true });
  }
};
</script>
