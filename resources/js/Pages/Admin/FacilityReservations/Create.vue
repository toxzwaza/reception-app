<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">施設予約登録</h2>
    </template>

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">施設予約情報</h3>

            <!-- タイトル -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                予定タイトル <span class="text-red-500">*</span>
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="例: 営業会議"
              />
            </div>

            <!-- バッジ -->
            <div>
              <label for="badge" class="block text-sm font-medium text-gray-700 mb-1">
                バッジ
              </label>
              <input
                id="badge"
                v-model="form.badge"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="例: 重要"
              />
            </div>

            <!-- 説明URL -->
            <div>
              <label for="description_url" class="block text-sm font-medium text-gray-700 mb-1">
                説明URL
              </label>
              <input
                id="description_url"
                v-model="form.description_url"
                type="url"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="https://example.com"
              />
            </div>

            <!-- 予定参加者 -->
            <div class="border-t border-gray-300 pt-4">
              <h4 class="text-md font-semibold text-gray-900 mb-3">予定参加者</h4>
              <p class="text-sm text-gray-600 mb-4">
                この予定に参加するメンバーを選択してください。
              </p>

              <!-- メンバー絞り込み選択 -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  メンバーを絞り込み
                </label>
                
                <div class="flex gap-2 mb-2">
                  <!-- 部署選択 -->
                  <div class="flex-1">
                    <select
                      v-model="selectedGroupId"
                      @change="loadGroupUsers"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                      <option value="">部署を選択...</option>
                      <option v-for="group in groups" :key="group.id" :value="group.id">
                        {{ group.name }}
                      </option>
                    </select>
                  </div>
                  
                  <!-- プロジェクトグループ選択 -->
                  <div class="flex-1">
                    <select
                      v-model.number="selectedProjectGroupId"
                      @change="loadProjectGroupUsers"
                      class="w-full rounded-md border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                    >
                      <option :value="null">プロジェクトグループを選択...</option>
                      <option v-for="projectGroup in projectGroups" :key="projectGroup.id" :value="projectGroup.id">
                        {{ projectGroup.name }} ({{ projectGroup.users?.length || 0 }}名)
                      </option>
                    </select>
                  </div>
                </div>
                
                <p class="text-xs text-gray-600">
                  部署またはプロジェクトグループを選択すると、そのメンバーが下に表示されます
                </p>
              </div>

              <!-- 部署のユーザー一覧 -->
              <div v-if="groupUsers.length > 0" class="mb-3">
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-sm font-medium text-gray-700">
                    ユーザーを選択
                  </label>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="selectAllUsers"
                      class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1 rounded-md"
                    >
                      全選択
                    </button>
                    <button
                      type="button"
                      @click="deselectAllUsers"
                      class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md"
                    >
                      全解除
                    </button>
                  </div>
                </div>
                <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md bg-white">
                  <label
                    v-for="user in groupUsers"
                    :key="user.id"
                    class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                  >
                    <input
                      type="checkbox"
                      :value="user.id"
                      v-model="selectedParticipants"
                      class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
                    />
                    <div class="flex-1">
                      <div class="font-medium text-gray-900">{{ user.name }}</div>
                      <div v-if="user.email" class="text-xs text-gray-500">{{ user.email }}</div>
                    </div>
                  </label>
                </div>
              </div>

              <!-- 選択済み参加者の表示 -->
              <div v-if="allParticipants.length > 0" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  選択済み参加者 ({{ allParticipants.length }}名)
                </label>
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="participant in allParticipants"
                    :key="participant.id"
                    :class="['inline-flex items-center px-3 py-1 rounded-full text-sm', getUserColor(participant.id).badgeBg, getUserColor(participant.id).badgeBorder, 'border-2']"
                  >
                    <div 
                      :class="['w-2 h-2 rounded-full mr-2', getUserColor(participant.id).bg]"
                    ></div>
                    <span :class="['font-medium', getUserColor(participant.id).text]">{{ participant.name }}</span>
                    <button
                      type="button"
                      @click="removeParticipant(participant.id)"
                      :class="['ml-2 hover:opacity-70', getUserColor(participant.id).text]"
                    >
                      ✕
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- 施設と時間選択 -->
            <div class="border-t border-gray-300 pt-4">
              <h4 class="text-md font-semibold text-gray-900 mb-3">施設と時間を選択</h4>
              <p class="text-sm text-gray-600 mb-4">
                カレンダーから施設と時間帯を選択してください。
              </p>

              <!-- カレンダーコンポーネント -->
              <FacilityScheduleCalendar
                :facilities="facilities"
                :participant-schedules="participantSchedules"
                :user-color-map="userColorMap"
                v-model="calendarSelection"
              />
            </div>

            <!-- ボタン -->
            <div class="flex justify-between pt-4">
              <Link
                :href="route('admin.facility-reservations.index')"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="submit"
                :disabled="form.processing || !calendarSelection"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50 font-semibold"
              >
                登録
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
import { ref, computed, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FacilityScheduleCalendar from '@/Components/FacilityScheduleCalendar.vue';

const props = defineProps({
  facilities: Array,
  staffMembers: Array,
  groups: Array,
  projectGroups: Array,
});

const form = useForm({
  title: '',
  facility_id: '',
  start_datetime: '',
  end_datetime: '',
  badge: '',
  description_url: '',
  participants: [],
});

// カレンダーからの選択
const calendarSelection = ref(null);

// 参加者管理
const selectedGroupId = ref('');
const selectedProjectGroupId = ref(null);
const groupUsers = ref([]);
const selectedParticipants = ref([]);
const participantSchedules = ref([]);

// カレンダーの選択が変更されたら、formを更新
watch(calendarSelection, (newValue) => {
  if (newValue) {
    form.facility_id = newValue.facility_id;
    form.start_datetime = `${newValue.start_date} ${newValue.start_time}`;
    form.end_datetime = `${newValue.end_date} ${newValue.end_time}`;
  }
});

// 選択された参加者が変更されたら、ユーザー予定を取得
watch(selectedParticipants, async (newValue) => {
  await loadParticipantSchedules();
}, { deep: true });

// 部署のユーザーを読み込む
const loadGroupUsers = async () => {
  // プロジェクトグループの選択をクリア
  selectedProjectGroupId.value = null;
  
  if (!selectedGroupId.value) {
    groupUsers.value = [];
    return;
  }

  try {
    const response = await window.axios.get(`/admin/groups/${selectedGroupId.value}/users`);
    groupUsers.value = response.data.users;
  } catch (error) {
    console.error('Failed to load group users:', error);
    groupUsers.value = [];
  }
};

// プロジェクトグループのユーザーを読み込む
const loadProjectGroupUsers = () => {
  // 部署の選択をクリア
  selectedGroupId.value = '';
  
  if (!selectedProjectGroupId.value) {
    groupUsers.value = [];
    return;
  }

  const projectGroup = props.projectGroups.find(pg => pg.id == selectedProjectGroupId.value);
  
  if (!projectGroup || !projectGroup.users) {
    groupUsers.value = [];
    return;
  }

  // プロジェクトグループのユーザーを表示（削除フラグがないユーザーのみ）
  // del_flgは0が有効、1が削除済みだが、型が数値か文字列か、またはnullの可能性があるため柔軟に判定
  groupUsers.value = projectGroup.users.filter(user => user.del_flg == 0 || user.del_flg === null || user.del_flg === '0');
};

// 参加者の予定を読み込む
const loadParticipantSchedules = async () => {
  const userIds = [...selectedParticipants.value];
  
  if (userIds.length === 0) {
    participantSchedules.value = [];
    return;
  }

  try {
    const startDate = new Date();
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 14);

    const response = await window.axios.post('/admin/user-schedules', {
      user_ids: userIds,
      start_date: startDate.toISOString().split('T')[0],
      end_date: endDate.toISOString().split('T')[0],
    });
    
    participantSchedules.value = response.data.schedules || [];
  } catch (error) {
    console.error('Failed to load participant schedules:', error);
    participantSchedules.value = [];
  }
};

// 参加者用のカラーパレット
const participantColors = [
  { bg: 'bg-blue-400', border: 'border-blue-500', text: 'text-blue-900', badgeBg: 'bg-blue-100', badgeBorder: 'border-blue-300', hex: '#60a5fa' },
  { bg: 'bg-green-400', border: 'border-green-500', text: 'text-green-900', badgeBg: 'bg-green-100', badgeBorder: 'border-green-300', hex: '#4ade80' },
  { bg: 'bg-purple-400', border: 'border-purple-500', text: 'text-purple-900', badgeBg: 'bg-purple-100', badgeBorder: 'border-purple-300', hex: '#c084fc' },
  { bg: 'bg-yellow-400', border: 'border-yellow-500', text: 'text-yellow-900', badgeBg: 'bg-yellow-100', badgeBorder: 'border-yellow-300', hex: '#facc15' },
  { bg: 'bg-pink-400', border: 'border-pink-500', text: 'text-pink-900', badgeBg: 'bg-pink-100', badgeBorder: 'border-pink-300', hex: '#f472b6' },
  { bg: 'bg-indigo-400', border: 'border-indigo-500', text: 'text-indigo-900', badgeBg: 'bg-indigo-100', badgeBorder: 'border-indigo-300', hex: '#818cf8' },
  { bg: 'bg-orange-400', border: 'border-orange-500', text: 'text-orange-900', badgeBg: 'bg-orange-100', badgeBorder: 'border-orange-300', hex: '#fb923c' },
  { bg: 'bg-teal-400', border: 'border-teal-500', text: 'text-teal-900', badgeBg: 'bg-teal-100', badgeBorder: 'border-teal-300', hex: '#2dd4bf' },
];

const userColorMap = ref({});

// 全参加者を取得
const allParticipants = computed(() => {
  const participants = [];
  const participantIds = new Set();

  selectedParticipants.value.forEach(participantId => {
    if (!participantIds.has(participantId)) {
      let user = groupUsers.value.find(u => u.id === participantId);
      if (!user) {
        user = props.staffMembers.find(s => s.id === participantId);
      }
      if (user) {
        participants.push(user);
        participantIds.add(user.id);
      }
    }
  });

  // 色を割り当て
  participants.forEach((participant, index) => {
    if (!userColorMap.value[participant.id]) {
      userColorMap.value[participant.id] = participantColors[index % participantColors.length];
    }
  });

  return participants;
});

// ユーザーIDから色を取得
const getUserColor = (userId) => {
  return userColorMap.value[userId] || participantColors[0];
};

// 参加者を削除
const removeParticipant = (participantId) => {
  const index = selectedParticipants.value.indexOf(participantId);
  if (index > -1) {
    selectedParticipants.value.splice(index, 1);
  }
};

// 全ユーザーを選択
const selectAllUsers = () => {
  groupUsers.value.forEach(user => {
    if (!selectedParticipants.value.includes(user.id)) {
      selectedParticipants.value.push(user.id);
    }
  });
};

// 全ユーザーの選択を解除
const deselectAllUsers = () => {
  const userIds = groupUsers.value.map(u => u.id);
  selectedParticipants.value = selectedParticipants.value.filter(id => !userIds.includes(id));
};

const submit = () => {
  if (!form.title) {
    alert('予定タイトルを入力してください。');
    return;
  }

  if (!calendarSelection.value) {
    alert('カレンダーから施設と時間帯を選択してください。');
    return;
  }

  form.participants = selectedParticipants.value;
  form.post(route('admin.facility-reservations.store'));
};
</script>
