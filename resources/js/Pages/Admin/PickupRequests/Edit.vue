<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        集荷依頼 編集
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
              <p class="mt-1 text-xs text-slate-500">
                現在の依頼者：<span class="font-medium text-slate-700">{{ form.requester_name || '—' }}</span>
              </p>
              <div v-if="form.errors.requester_name" class="mt-1 text-sm text-rose-600">{{ form.errors.requester_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">物品 <span class="text-rose-500">*</span></label>
              <input v-model="form.item" type="text" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="form.errors.item" class="mt-1 text-sm text-rose-600">{{ form.errors.item }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">物品画像</label>
              <input
                type="file"
                accept="image/*"
                @change="onImageChange"
                class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
              />
              <div v-if="imagePreview || currentImageUrl" class="mt-2">
                <img :src="imagePreview || currentImageUrl" alt="物品画像" class="h-40 rounded-lg border border-slate-200 object-contain" />
                <p class="mt-1 text-xs text-slate-400">{{ imagePreview ? '新しい画像（保存で差し替え）' : '現在の画像' }}</p>
              </div>
              <div v-if="form.errors.item_image" class="mt-1 text-sm text-rose-600">{{ form.errors.item_image }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">置き場所</label>
              <input v-model="form.storage_location" type="text" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="form.errors.storage_location" class="mt-1 text-sm text-rose-600">{{ form.errors.storage_location }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">問い合わせ電話番号</label>
              <input v-model="form.contact_phone" type="tel" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="form.errors.contact_phone" class="mt-1 text-sm text-rose-600">{{ form.errors.contact_phone }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">備考</label>
              <textarea v-model="form.memo" rows="3" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link :href="route('admin.pickup-requests.index')" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition">
                キャンセル
              </Link>
              <div class="flex gap-2">
                <Link :href="route('admin.pickup-requests.slip', pickupRequest.id)" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition">
                  伝票を表示
                </Link>
                <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition">
                  更新
                </button>
              </div>
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
  pickupRequest: { type: Object, required: true },
  itemImageUrl: { type: String, default: null },
  staffMembers: { type: Array, default: () => [] },
  groups: { type: Array, default: () => [] },
});

const form = useForm({
  requester_name: props.pickupRequest.requester_name,
  requester_group_id: props.pickupRequest.requester_group_id || '',
  item: props.pickupRequest.item,
  item_image: null,
  storage_location: props.pickupRequest.storage_location || '',
  contact_phone: props.pickupRequest.contact_phone || '',
  memo: props.pickupRequest.memo || '',
});

// 物品画像（既存＋差し替えプレビュー）
const currentImageUrl = props.itemImageUrl;
const imagePreview = ref(null);
const onImageChange = (e) => {
  const file = e.target.files?.[0] || null;
  form.item_image = file;
  imagePreview.value = file ? URL.createObjectURL(file) : null;
};

// 選択中の担当者（既存の依頼者名に一致する User を初期選択）
const selectedStaffId = ref(
  props.staffMembers.find((s) => s.name === props.pickupRequest.requester_name)?.id ?? ''
);

const filteredStaff = computed(() => {
  if (!form.requester_group_id) return props.staffMembers;
  return props.staffMembers.filter((s) => String(s.group_id) === String(form.requester_group_id));
});

const onGroupChange = () => {
  selectedStaffId.value = '';
  form.requester_name = '';
  const g = props.groups.find((x) => String(x.id) === String(form.requester_group_id));
  if (g && g.phone_number) {
    form.contact_phone = g.phone_number;
  }
};

const onStaffChange = () => {
  const s = props.staffMembers.find((x) => String(x.id) === String(selectedStaffId.value));
  form.requester_name = s ? s.name : '';
};

const submit = () => {
  // ファイルを含むため forceFormData（put はInertiaが POST+_method=PUT に変換）
  form.put(route('admin.pickup-requests.update', props.pickupRequest.id), { forceFormData: true });
};
</script>
