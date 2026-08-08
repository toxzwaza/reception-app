<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        担当者呼出管理：{{ staff.name }}
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
          <dl class="mb-6 grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-sm">
            <div>
              <dt class="text-xs text-slate-500">社員番号</dt>
              <dd class="mt-0.5 font-semibold tabular-nums text-slate-800">{{ staff.emp_no }}</dd>
            </div>
            <div>
              <dt class="text-xs text-slate-500">部署</dt>
              <dd class="mt-0.5 font-semibold text-slate-800">{{ staff.group_name ?? '—' }}</dd>
            </div>
          </dl>

          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label for="name_kana" class="block text-sm font-semibold text-slate-700 mb-1.5">ヨミ（かな検索用）</label>
              <input
                id="name_kana"
                v-model="form.name_kana"
                type="text"
                placeholder="例：アキオカ タロウ"
                class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
              />
              <p class="mt-1 text-xs text-slate-500">カタカナ・ひらがなどちらで入力しても保存時にカタカナへ統一されます。</p>
              <p v-if="form.errors.name_kana" class="mt-1 text-sm text-red-600">{{ form.errors.name_kana }}</p>
            </div>

            <div>
              <label for="mobile_phone" class="block text-sm font-semibold text-slate-700 mb-1.5">個人携帯番号</label>
              <input
                id="mobile_phone"
                v-model="form.mobile_phone"
                type="tel"
                placeholder="例：+819012345678 または 090-1234-5678"
                class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
              />
              <p class="mt-1 text-xs text-slate-500">登録すると受付端末の担当者検索に「携帯」ボタンが表示されます。空欄にすると部署電話のみになります。</p>
              <p v-if="form.errors.mobile_phone" class="mt-1 text-sm text-red-600">{{ form.errors.mobile_phone }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <label class="flex items-start gap-3">
                <input
                  v-model="form.call_search_flg"
                  type="checkbox"
                  class="mt-0.5 h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                />
                <span>
                  <span class="block text-sm font-semibold text-slate-700">受付の担当者検索に表示する</span>
                  <span class="mt-0.5 block text-xs text-slate-500">オフにすると受付端末の検索結果に表示されず、呼び出しもできなくなります。</span>
                </span>
              </label>
              <p v-if="form.errors.call_search_flg" class="mt-1 text-sm text-red-600">{{ form.errors.call_search_flg }}</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <Link
                :href="route('admin.staff-phones.index')"
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-50"
              >
                {{ form.processing ? '保存中...' : '保存する' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  staff: { type: Object, required: true },
});

const form = useForm({
  name_kana: props.staff.name_kana ?? '',
  mobile_phone: props.staff.mobile_phone ?? '',
  call_search_flg: Boolean(props.staff.call_search_flg),
});

const submit = () => {
  form.put(route('admin.staff-phones.update', props.staff.id));
};
</script>
