<template>
  <AdminLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">事前アポイント新規登録</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <!-- ステップインジケーター -->
        <div class="mb-6 flex justify-center">
          <div class="flex items-center space-x-4">
            <div class="flex items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-white font-bold', currentStep === 1 ? 'bg-indigo-600' : 'bg-indigo-400']">
                1
              </div>
              <span class="ml-2 text-sm font-medium text-gray-700">アポイント情報</span>
            </div>
            <div class="w-16 h-1 bg-gray-300"></div>
            <div class="flex items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-white font-bold', currentStep === 2 ? 'bg-indigo-600' : currentStep > 2 ? 'bg-indigo-400' : 'bg-gray-300']">
                2
              </div>
              <span class="ml-2 text-sm font-medium text-gray-700">施設予約</span>
            </div>
            <div class="w-16 h-1 bg-gray-300"></div>
            <div class="flex items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-white font-bold', currentStep === 3 ? 'bg-indigo-600' : 'bg-gray-300']">
                3
              </div>
              <span class="ml-2 text-sm font-medium text-gray-700">確認</span>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="handleFormSubmit" class="p-6 space-y-6">
            <!-- ステップ1: アポイント情報 -->
            <div v-show="currentStep === 1" class="space-y-6">
              <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">アポイント情報</h3>
            
            <!-- 会社名 -->
            <div>
              <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                会社名 <span class="text-red-500">*</span>
              </label>
              <input
                id="company_name"
                v-model="form.company_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.company_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.company_name }}
              </p>
            </div>

            <!-- 訪問者名 -->
            <div>
              <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-1">
                訪問者名 <span class="text-red-500">*</span>
              </label>
              <input
                id="visitor_name"
                v-model="form.visitor_name"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_name }}
              </p>
            </div>

            <!-- メールアドレス -->
            <div>
              <label for="visitor_email" class="block text-sm font-medium text-gray-700 mb-1">
                メールアドレス
              </label>
              <input
                id="visitor_email"
                v-model="form.visitor_email"
                type="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_email" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_email }}
              </p>
            </div>

            <!-- 電話番号 -->
            <div>
              <label for="visitor_phone" class="block text-sm font-medium text-gray-700 mb-1">
                電話番号
              </label>
              <input
                id="visitor_phone"
                v-model="form.visitor_phone"
                type="tel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visitor_phone" class="mt-1 text-sm text-red-600">
                {{ form.errors.visitor_phone }}
              </p>
            </div>

            <!-- 担当スタッフ -->
            <div>
              <label for="staff_member_id" class="block text-sm font-medium text-gray-700 mb-1">
                担当スタッフ <span class="text-red-500">*</span>
              </label>
              <select
                id="staff_member_id"
                v-model="form.staff_member_id"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option value="">選択してください</option>
                <option v-for="staff in staffMembers" :key="staff.id" :value="staff.id">
                  {{ staff.name }}
                </option>
              </select>
              <p v-if="form.errors.staff_member_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.staff_member_id }}
              </p>
            </div>

            <!-- 訪問日 -->
            <div>
              <label for="visit_date" class="block text-sm font-medium text-gray-700 mb-1">
                訪問日 <span class="text-red-500">*</span>
              </label>
              <input
                id="visit_date"
                v-model="form.visit_date"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visit_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.visit_date }}
              </p>
            </div>

            <!-- 訪問時刻 -->
            <div>
              <label for="visit_time" class="block text-sm font-medium text-gray-700 mb-1">
                訪問時刻 <span class="text-red-500">*</span>
              </label>
              <input
                id="visit_time"
                v-model="form.visit_time"
                type="time"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <p v-if="form.errors.visit_time" class="mt-1 text-sm text-red-600">
                {{ form.errors.visit_time }}
              </p>
            </div>

            <!-- 訪問目的 -->
            <div>
              <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">
                訪問目的
              </label>
              <textarea
                id="purpose"
                v-model="form.purpose"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <p v-if="form.errors.purpose" class="mt-1 text-sm text-red-600">
                {{ form.errors.purpose }}
              </p>
            </div>

            <!-- ステップ1のボタン -->
            <div class="flex justify-end space-x-3 pt-4">
              <Link 
                :href="route('admin.appointments.index')" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
              >
                キャンセル
              </Link>
              <button
                type="button"
                @click="goToFacilityReservation"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md"
              >
                次へ（施設予約）
              </button>
            </div>
            </div>

            <!-- ステップ2: 施設予約情報 -->
            <div v-show="currentStep === 2" class="space-y-6">
              <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">施設予約情報</h3>
              
              <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-sm text-blue-800">
                  会議室などの施設を予約する場合は、まず予定の詳細情報を入力してから、カレンダーで施設と時間帯を選択してください。<br>
                  施設予約が不要な場合は、「施設予約なしで次へ」をクリックしてください。
                </p>
              </div>

              <!-- タイトル -->
              <div>
                <label for="schedule_title" class="block text-sm font-medium text-gray-700 mb-1">
                  予定タイトル <span class="text-red-500">*</span>
                </label>
                <input
                  id="schedule_title"
                  v-model="facilityForm.title"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="例: 〇〇社との打ち合わせ"
                />
              </div>

              <!-- バッジ -->
              <div>
                <label for="badge" class="block text-sm font-medium text-gray-700 mb-1">
                  バッジ（ラベル）
                </label>
                <input
                  id="badge"
                  v-model="facilityForm.badge"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="例: 重要、定例会議"
                />
              </div>

              <!-- 説明URL -->
              <div>
                <label for="description_url" class="block text-sm font-medium text-gray-700 mb-1">
                  説明URL
                </label>
                <input
                  id="description_url"
                  v-model="facilityForm.description_url"
                  type="url"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="https://"
                />
              </div>

              <!-- 参加者選択 -->
              <div class="border-t border-gray-300 pt-4">
                <h4 class="text-md font-semibold text-gray-900 mb-3">予定参加者</h4>
                
                <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4 text-sm text-blue-800">
                  ℹ️ 担当スタッフは自動的に参加者に追加されます。追加で参加者を選択する場合は、以下から部署またはプロジェクトグループを選んでユーザーを追加してください。
                </div>

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
                        :disabled="user.id === form.staff_member_id"
                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3"
                      />
                      <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">
                          {{ user.name }}
                          <span v-if="user.id === form.staff_member_id" class="text-xs text-indigo-600 ml-2">
                            (担当スタッフ)
                          </span>
                        </div>
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
                      <span v-if="participant.id === form.staff_member_id" :class="['ml-1 text-xs', getUserColor(participant.id).text]">
                        (担当)
                      </span>
                      <button
                        v-if="participant.id !== form.staff_member_id"
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

              <!-- ステップ2のボタン -->
              <div class="flex justify-between pt-4">
                <button
                  type="button"
                  @click="currentStep = 1"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
                >
                  戻る
                </button>
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="skipFacilityReservation"
                    class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-md"
                  >
                    施設予約なしで次へ
                  </button>
                  <button
                    type="button"
                    @click="goToConfirmation"
                    :disabled="!calendarSelection"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md disabled:opacity-50"
                  >
                    次へ（確認）
                  </button>
                </div>
              </div>
            </div>

            <!-- ステップ3: 確認 -->
            <div v-show="currentStep === 3" class="space-y-6">
              <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">登録内容の確認</h3>

              <!-- アポイント情報 -->
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">アポイント情報</h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div class="text-gray-600">会社名:</div>
                  <div class="text-gray-900 font-medium">{{ form.company_name }}</div>
                  <div class="text-gray-600">訪問者名:</div>
                  <div class="text-gray-900 font-medium">{{ form.visitor_name }}</div>
                  <div class="text-gray-600">メールアドレス:</div>
                  <div class="text-gray-900 font-medium">{{ form.visitor_email || '（未入力）' }}</div>
                  <div class="text-gray-600">電話番号:</div>
                  <div class="text-gray-900 font-medium">{{ form.visitor_phone || '（未入力）' }}</div>
                  <div class="text-gray-600">担当スタッフ:</div>
                  <div class="text-gray-900 font-medium">{{ getStaffName(form.staff_member_id) }}</div>
                  <div class="text-gray-600">訪問日:</div>
                  <div class="text-gray-900 font-medium">{{ form.visit_date }}</div>
                  <div class="text-gray-600">訪問時刻:</div>
                  <div class="text-gray-900 font-medium">{{ form.visit_time }}</div>
                  <div class="text-gray-600">訪問目的:</div>
                  <div class="text-gray-900 font-medium">{{ form.purpose || '（未入力）' }}</div>
                </div>
              </div>

              <!-- 施設予約情報 -->
              <div v-if="facilityForm.needs_facility" class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">施設予約情報</h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div class="text-gray-600">施設:</div>
                  <div class="text-gray-900 font-medium">{{ getFacilityName(facilityForm.facility_id) }}</div>
                  <div class="text-gray-600">予定タイトル:</div>
                  <div class="text-gray-900 font-medium">{{ facilityForm.title }}</div>
                  <div class="text-gray-600">開始日時:</div>
                  <div class="text-gray-900 font-medium">{{ facilityForm.start_date }} {{ facilityForm.start_time }}</div>
                  <div class="text-gray-600">終了日時:</div>
                  <div class="text-gray-900 font-medium">{{ facilityForm.end_date }} {{ facilityForm.end_time }}</div>
                  <div v-if="facilityForm.badge" class="text-gray-600">バッジ:</div>
                  <div v-if="facilityForm.badge" class="text-gray-900 font-medium">{{ facilityForm.badge }}</div>
                  <div v-if="facilityForm.description_url" class="text-gray-600">説明URL:</div>
                  <div v-if="facilityForm.description_url" class="text-gray-900 font-medium">{{ facilityForm.description_url }}</div>
                </div>
                
                <!-- 参加者一覧 -->
                <div v-if="allParticipants.length > 0" class="mt-4 pt-3 border-t border-gray-200">
                  <div class="text-sm font-medium text-gray-700 mb-2">
                    予定参加者 ({{ allParticipants.length }}名)
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="participant in allParticipants"
                      :key="participant.id"
                      :class="['inline-flex items-center px-2 py-1 rounded text-xs border-2', getUserColor(participant.id).badgeBg, getUserColor(participant.id).badgeBorder]"
                    >
                      <div 
                        :class="['w-1.5 h-1.5 rounded-full mr-1.5', getUserColor(participant.id).bg]"
                      ></div>
                      <span :class="['font-medium', getUserColor(participant.id).text]">{{ participant.name }}</span>
                      <span v-if="participant.id === form.staff_member_id" :class="['ml-1', getUserColor(participant.id).text]">
                        (担当)
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="bg-gray-50 rounded-lg p-4 text-center text-gray-600">
                施設予約なし
              </div>

              <!-- ステップ3のボタン -->
              <div class="flex justify-between pt-4">
                <button
                  type="button"
                  @click="currentStep = 2"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md"
                >
                  戻る
                </button>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md disabled:opacity-50 font-semibold"
                >
                  確定・登録
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- メール送信確認ダイアログ -->
    <div v-if="showEmailDialog" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">メール送信確認</h3>
          <div class="mt-4">
            <p class="text-sm text-gray-500">
              訪問者に受付番号とQRコードを含む確認メールを送信しますか？
            </p>
            <p class="text-xs text-gray-400 mt-2">
              送信先: {{ form.visitor_email }}
            </p>
          </div>
          <div class="flex justify-center space-x-4 mt-6">
            <button
              @click="finalSubmit(false)"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm"
            >
              送信しない
            </button>
            <button
              @click="finalSubmit(true)"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm"
            >
              メール送信
            </button>
          </div>
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
  staffMembers: Array,
  facilities: Array,
  groups: Array,
  projectGroups: Array,
});

// ステップ管理
const currentStep = ref(1);

// アポイントフォーム
const form = useForm({
  company_name: '',
  visitor_name: '',
  visitor_email: '',
  visitor_phone: '',
  staff_member_id: '',
  visit_date: '',
  visit_time: '',
  purpose: '',
  send_email: false,
});

// 施設予約フォーム
const facilityForm = ref({
  needs_facility: false,
  facility_id: '',
  title: '',
  start_date: '',
  start_time: '',
  end_date: '',
  end_time: '',
  badge: '',
  description_url: '',
});

// カレンダーからの選択
const calendarSelection = ref(null);

// 参加者管理
const selectedGroupId = ref('');
const selectedProjectGroupId = ref(null);
const groupUsers = ref([]);
const selectedParticipants = ref([]);
const participantSchedules = ref([]);

const showEmailDialog = ref(false);

// カレンダーの選択が変更されたら、facilityFormを更新
watch(calendarSelection, (newValue) => {
  if (newValue) {
    facilityForm.value.facility_id = newValue.facility_id;
    facilityForm.value.start_date = newValue.start_date;
    facilityForm.value.start_time = newValue.start_time;
    facilityForm.value.end_date = newValue.end_date;
    facilityForm.value.end_time = newValue.end_time;
    
    // 予定タイトルが未入力で訪問目的が入力されている場合、デフォルト値として設定
    if (!facilityForm.value.title && form.purpose) {
      facilityForm.value.title = form.purpose;
    }
  }
});

// 選択された参加者が変更されたら、ユーザー予定を取得
watch(selectedParticipants, async (newValue) => {
  await loadParticipantSchedules();
}, { deep: true });

// 担当者が変更されたら、ユーザー予定を再取得
watch(() => form.staff_member_id, async (newValue) => {
  if (currentStep.value === 2) {
    await loadParticipantSchedules();
  }
});

// ステップ1からステップ2へ
const goToFacilityReservation = async () => {
  // 必須項目のバリデーション
  if (!form.company_name || !form.visitor_name || !form.staff_member_id || !form.visit_date || !form.visit_time) {
    alert('必須項目を入力してください。');
    return;
  }
  
  // 訪問目的が入力されている場合、予定タイトルのデフォルト値として設定
  if (form.purpose && !facilityForm.value.title) {
    facilityForm.value.title = form.purpose;
  }
  
  currentStep.value = 2;
  
  // 担当者の予定を初期読み込み
  await loadParticipantSchedules();
};

// 施設予約をスキップ
const skipFacilityReservation = () => {
  facilityForm.value.needs_facility = false;
  currentStep.value = 3;
};

// ステップ2からステップ3へ
const goToConfirmation = () => {
  // 予定タイトルのバリデーション
  if (!facilityForm.value.title) {
    alert('予定タイトルを入力してください。');
    return;
  }
  
  // 施設と時間の選択チェック
  if (!calendarSelection.value) {
    alert('カレンダーから施設と時間帯を選択してください。\n施設予約が不要な場合は「施設予約なしで次へ」をクリックしてください。');
    return;
  }
  
  facilityForm.value.needs_facility = true;
  currentStep.value = 3;
};

// フォーム送信（ステップ3の確定ボタン）
const handleFormSubmit = () => {
  // メールアドレスが入力されている場合は送信確認ダイアログを表示
  if (form.visitor_email) {
    showEmailDialog.value = true;
  } else {
    // メールアドレスがない場合は直接送信
    finalSubmit(false);
  }
};

// 最終的な送信処理
const finalSubmit = (sendEmail) => {
  showEmailDialog.value = false;
  form.send_email = sendEmail;
  
  // 施設予約データをフォームに追加
  const submitData = {
    ...form.data(),
    facility_reservation: facilityForm.value.needs_facility ? {
      facility_id: facilityForm.value.facility_id,
      title: facilityForm.value.title,
      start_datetime: `${facilityForm.value.start_date} ${facilityForm.value.start_time}`,
      end_datetime: `${facilityForm.value.end_date} ${facilityForm.value.end_time}`,
      badge: facilityForm.value.badge,
      description_url: facilityForm.value.description_url,
      participants: selectedParticipants.value.filter(id => id !== form.staff_member_id), // 担当スタッフ以外
    } : null,
  };
  
  form.transform(() => submitData).post(route('admin.appointments.store'));
};

// スタッフ名を取得
const getStaffName = (staffId) => {
  const staff = props.staffMembers.find(s => s.id === staffId);
  return staff ? staff.name : '';
};

// 施設名を取得
const getFacilityName = (facilityId) => {
  const facility = props.facilities.find(f => f.id === facilityId);
  return facility ? facility.name : '';
};

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
    
    // 担当スタッフがこの部署に含まれる場合、自動的にチェック
    if (form.staff_member_id) {
      const staffInGroup = groupUsers.value.find(u => u.id === form.staff_member_id);
      if (staffInGroup && !selectedParticipants.value.includes(form.staff_member_id)) {
        selectedParticipants.value.push(form.staff_member_id);
      }
    }
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
  
  // 担当スタッフがこのプロジェクトグループに含まれる場合、自動的にチェック
  if (form.staff_member_id) {
    const staffInGroup = groupUsers.value.find(u => u.id === form.staff_member_id);
    if (staffInGroup && !selectedParticipants.value.includes(form.staff_member_id)) {
      selectedParticipants.value.push(form.staff_member_id);
    }
  }
};

// 参加者の予定を読み込む
const loadParticipantSchedules = async () => {
  // 担当者と選択された参加者のIDを収集
  const userIds = [];
  
  // 担当者を追加
  if (form.staff_member_id) {
    userIds.push(form.staff_member_id);
  }
  
  // 選択された参加者を追加
  if (selectedParticipants.value && selectedParticipants.value.length > 0) {
    selectedParticipants.value.forEach(id => {
      if (!userIds.includes(id)) {
        userIds.push(id);
      }
    });
  }
  
  if (userIds.length === 0) {
    participantSchedules.value = [];
    return;
  }

  try {
    // 今日から2週間後までの予定を取得
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
  { bg: 'bg-blue-400', border: 'border-blue-500', text: 'text-blue-900', bgClass: 'bg-blue-400', badgeBg: 'bg-blue-100', badgeBorder: 'border-blue-300', hex: '#60a5fa' },
  { bg: 'bg-green-400', border: 'border-green-500', text: 'text-green-900', bgClass: 'bg-green-400', badgeBg: 'bg-green-100', badgeBorder: 'border-green-300', hex: '#4ade80' },
  { bg: 'bg-purple-400', border: 'border-purple-500', text: 'text-purple-900', bgClass: 'bg-purple-400', badgeBg: 'bg-purple-100', badgeBorder: 'border-purple-300', hex: '#c084fc' },
  { bg: 'bg-yellow-400', border: 'border-yellow-500', text: 'text-yellow-900', bgClass: 'bg-yellow-400', badgeBg: 'bg-yellow-100', badgeBorder: 'border-yellow-300', hex: '#facc15' },
  { bg: 'bg-pink-400', border: 'border-pink-500', text: 'text-pink-900', bgClass: 'bg-pink-400', badgeBg: 'bg-pink-100', badgeBorder: 'border-pink-300', hex: '#f472b6' },
  { bg: 'bg-indigo-400', border: 'border-indigo-500', text: 'text-indigo-900', bgClass: 'bg-indigo-400', badgeBg: 'bg-indigo-100', badgeBorder: 'border-indigo-300', hex: '#818cf8' },
  { bg: 'bg-orange-400', border: 'border-orange-500', text: 'text-orange-900', bgClass: 'bg-orange-400', badgeBg: 'bg-orange-100', badgeBorder: 'border-orange-300', hex: '#fb923c' },
  { bg: 'bg-teal-400', border: 'border-teal-500', text: 'text-teal-900', bgClass: 'bg-teal-400', badgeBg: 'bg-teal-100', badgeBorder: 'border-teal-300', hex: '#2dd4bf' },
];

// ユーザーIDに対する色のマッピング
const userColorMap = ref({});

// 全参加者を取得（担当スタッフ + 選択された参加者）
const allParticipants = computed(() => {
  const participants = [];
  const participantIds = new Set();

  // 担当スタッフを追加
  if (form.staff_member_id) {
    const staff = props.staffMembers.find(s => s.id === form.staff_member_id);
    if (staff) {
      participants.push(staff);
      participantIds.add(staff.id);
    }
  }

  // 選択された参加者を追加（重複を除く）
  selectedParticipants.value.forEach(participantId => {
    if (!participantIds.has(participantId)) {
      // groupUsersから探す
      let user = groupUsers.value.find(u => u.id === participantId);
      // 見つからない場合はstaffMembersから探す
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

// 全ユーザーの選択を解除（担当スタッフは除外）
const deselectAllUsers = () => {
  const userIds = groupUsers.value.map(u => u.id);
  selectedParticipants.value = selectedParticipants.value.filter(id => {
    // 担当スタッフは残す
    if (id === form.staff_member_id) return true;
    // 現在のリストにあるユーザーは除外
    return !userIds.includes(id);
  });
};
</script>



