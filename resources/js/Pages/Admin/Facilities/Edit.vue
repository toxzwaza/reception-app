<template>
  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
        施設編集
      </h2>
    </template>

    <div class="py-8">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <h3 class="text-lg font-semibold text-slate-800 border-b border-slate-200 pb-2">施設情報</h3>

            <!-- 施設名 -->
            <div>
              <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                施設名 <span class="text-rose-500">*</span>
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <div v-if="form.errors.name" class="text-sm text-rose-600 mt-1">{{ form.errors.name }}</div>
            </div>

            <!-- Outlook会議室の紐付け -->
            <div class="border-t border-slate-200 pt-4">
              <label class="block text-sm font-medium text-slate-700 mb-1">
                Outlook会議室の紐付け
              </label>
              <p class="text-sm text-slate-600 mb-3">
                予約をOutlookと連携させる場合は、対応する会議室を選択してください。
              </p>

              <select
                v-model="selectedRoom"
                @change="applyRoom"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2"
              >
                <option value="">― 会議室を選択 ―</option>
                <option v-for="room in outlookRooms" :key="room.emailAddress" :value="room.emailAddress">
                  {{ room.displayName }}（{{ room.emailAddress }}）
                </option>
              </select>

              <input
                id="outlook_resource_email"
                v-model="form.outlook_resource_email"
                type="email"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="meetingroomX@akioka-ltd.jp"
              />
              <p class="text-xs text-slate-500 mt-1">
                ※ 社用車など一覧に出ないリソースは、メールアドレスを直接入力してください。空欄の場合はOutlook連携なしになります。
              </p>
              <div v-if="outlookRooms.length === 0" class="text-xs text-amber-600 mt-1">
                ※ Outlook会議室一覧を取得できませんでした。メールアドレスを直接入力してください。
              </div>
              <div v-if="form.errors.outlook_resource_email" class="text-sm text-rose-600 mt-1">{{ form.errors.outlook_resource_email }}</div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4 border-t border-slate-200">
              <Link
                :href="route('admin.facilities.index')"
                class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2 rounded-lg font-medium transition"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg disabled:opacity-50 font-semibold transition"
              >
                更新
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
  facility: { type: Object, required: true },
  outlookRooms: { type: Array, default: () => [] },
});

const form = useForm({
  name: props.facility.name,
  outlook_resource_email: props.facility.outlook_resource_email || '',
});

// 既存のメールアドレスが会議室一覧にあれば、ドロップダウンの初期選択に反映
const selectedRoom = ref(
  props.outlookRooms.some((r) => r.emailAddress === props.facility.outlook_resource_email)
    ? props.facility.outlook_resource_email
    : ''
);
const applyRoom = () => {
  if (selectedRoom.value) {
    form.outlook_resource_email = selectedRoom.value;
  }
};

const submit = () => {
  form.put(route('admin.facilities.update', props.facility.id));
};
</script>
