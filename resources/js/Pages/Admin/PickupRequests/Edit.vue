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
              <input v-model="form.requester_name" type="text" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="form.errors.requester_name" class="mt-1 text-sm text-rose-600">{{ form.errors.requester_name }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">物品 <span class="text-rose-500">*</span></label>
              <input v-model="form.item" type="text" required class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
              <div v-if="form.errors.item" class="mt-1 text-sm text-rose-600">{{ form.errors.item }}</div>
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
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  pickupRequest: { type: Object, required: true },
});

const form = useForm({
  requester_name: props.pickupRequest.requester_name,
  item: props.pickupRequest.item,
  storage_location: props.pickupRequest.storage_location || '',
  contact_phone: props.pickupRequest.contact_phone || '',
  memo: props.pickupRequest.memo || '',
});

const submit = () => {
  form.put(route('admin.pickup-requests.update', props.pickupRequest.id));
};
</script>
