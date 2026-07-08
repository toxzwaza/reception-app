<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
          スタッフメンバー編集: {{ staffMember.user?.name || 'N/A' }}
        </h2>
        <div class="flex gap-2">
          <Link
            :href="route('admin.staff-members.index')"
            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold transition"
          >
            ← 一覧に戻る
          </Link>
          <Link
            :href="route('admin.staff-members.show', staffMember.id)"
            class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition"
          >
            詳細
          </Link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6">
              <!-- 情報表示 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">スタッフメンバー情報</h3>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                  <p class="text-sm text-slate-600 mb-2">
                    スタッフメンバーは、Userテーブルの情報を参照するため、ここでは編集できません。
                  </p>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <dt class="text-sm font-medium text-slate-500">スタッフID</dt>
                      <dd class="mt-1 text-sm text-slate-800">{{ staffMember.id }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-slate-500">ユーザーID</dt>
                      <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user_id }}</dd>
                    </div>
                    <div v-if="staffMember.user">
                      <dt class="text-sm font-medium text-slate-500">氏名</dt>
                      <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user.name }}</dd>
                    </div>
                    <div v-if="staffMember.user">
                      <dt class="text-sm font-medium text-slate-500">メールアドレス</dt>
                      <dd class="mt-1 text-sm text-slate-800">{{ staffMember.user.email }}</dd>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                <Link
                  :href="route('admin.staff-members.index')"
                  class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
                >
                  キャンセル
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition"
                >
                  {{ processing ? '更新中...' : '更新' }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  staffMember: Object,
  errors: Object,
});

const form = reactive({
  // StaffMemberはuser_idのみなので、フォームデータは不要
});

const processing = ref(false);

// フォーム送信
const submit = () => {
  processing.value = true;
  
  router.put(route('admin.staff-members.update', props.staffMember.id), form, {
    onFinish: () => {
      processing.value = false;
    }
  });
};
</script>
