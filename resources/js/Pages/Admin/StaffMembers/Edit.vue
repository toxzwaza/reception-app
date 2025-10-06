<template>
  <AdminLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          スタッフメンバー編集: {{ staffMember.user?.name || 'N/A' }}
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('admin.staff-members.index')"
            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
          >
            一覧に戻る
          </Link>
          <Link 
            :href="route('admin.staff-members.show', staffMember.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            詳細
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <!-- 情報表示 -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">スタッフメンバー情報</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                  <p class="text-sm text-gray-600 mb-2">
                    スタッフメンバーは、Userテーブルの情報を参照するため、ここでは編集できません。
                  </p>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">スタッフID</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ staffMember.id }}</dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">ユーザーID</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user_id }}</dd>
                    </div>
                    <div v-if="staffMember.user">
                      <dt class="text-sm font-medium text-gray-500">氏名</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user.name }}</dd>
                    </div>
                    <div v-if="staffMember.user">
                      <dt class="text-sm font-medium text-gray-500">メールアドレス</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ staffMember.user.email }}</dd>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 送信ボタン -->
              <div class="flex justify-end space-x-4">
                <Link 
                  :href="route('admin.staff-members.index')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  キャンセル
                </Link>
                <button 
                  type="submit"
                  :disabled="processing"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
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
