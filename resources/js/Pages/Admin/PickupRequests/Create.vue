<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        集荷依頼 新規登録
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">依頼者 <span class="text-rose-500">*</span></label>
              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <select v-model="form.requester_group_id" @change="onGroupChange" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="">部署を選択</option>
                  <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
                <select v-model="selectedStaffId" @change="onStaffChange" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="">担当者を選択（{{ filteredStaff.length }}名）</option>
                  <option v-for="s in filteredStaff" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
              <p class="mt-1 text-xs text-slate-500">部署を選ぶと担当者を絞り込めます。</p>
              <div v-if="form.errors.requester_name" class="mt-1 text-sm text-rose-600">{{ form.errors.requester_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">物品 <span class="text-rose-500">*</span></label>
              <input v-model="form.item" type="text" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="例: 段ボール2箱（書類）" />
              <div v-if="form.errors.item" class="mt-1 text-sm text-rose-600">{{ form.errors.item }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">置き場所</label>
              <input v-model="form.storage_location" type="text" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="例: 1F 集荷場所 A" />
              <div v-if="form.errors.storage_location" class="mt-1 text-sm text-rose-600">{{ form.errors.storage_location }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">問い合わせ電話番号</label>
              <input v-model="form.contact_phone" type="tel" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="例: 内線 123 / 0864-00-0000" />
              <div v-if="form.errors.contact_phone" class="mt-1 text-sm text-rose-600">{{ form.errors.contact_phone }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">備考</label>
              <textarea v-model="form.memo" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="ドライバーへの連絡事項など"></textarea>
            </div>

            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link :href="route('admin.pickup-requests.index')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition">
                キャンセル
              </Link>
              <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition">
                登録して伝票を発行
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  staffMembers: { type: Array, default: () => [] },
  groups: { type: Array, default: () => [] },
});

const form = useForm({
  requester_name: '',
  requester_group_id: '',
  item: '',
  storage_location: '',
  contact_phone: '',
  memo: '',
});

// 選択中の担当者（User.id）
const selectedStaffId = ref('');

// 選択部署で担当者を絞り込み
const filteredStaff = computed(() => {
  if (!form.requester_group_id) return props.staffMembers;
  return props.staffMembers.filter((s) => String(s.group_id) === String(form.requester_group_id));
});

// 部署変更：担当者選択をリセットし、部署の電話番号を問い合わせ先の既定にセット（変更可）
const onGroupChange = () => {
  selectedStaffId.value = '';
  form.requester_name = '';
  const g = props.groups.find((x) => String(x.id) === String(form.requester_group_id));
  if (g && g.phone_number) {
    form.contact_phone = g.phone_number;
  }
};

// 担当者変更：依頼者名にセット
const onStaffChange = () => {
  const s = props.staffMembers.find((x) => String(x.id) === String(selectedStaffId.value));
  form.requester_name = s ? s.name : '';
};

const submit = () => {
  form.post(route('admin.pickup-requests.store'));
};
</script>
