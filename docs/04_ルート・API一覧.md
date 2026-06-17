# 04. ルート・API一覧

> route groupのprefix/middlewareを反映した実URI。
> ミドルウェア `localstorage.auth` = 独自ミドルウェア `CheckLocalStorageAuth`（`/admin/*` 保護）。

---

## web.php（公開受付系）

| メソッド | URI | コントローラ@アクション | ルート名 | 認証 |
|---------|-----|----------------------|---------|------|
| GET | / | HomeController@index | home | なし |
| GET | /facility-timeline | FacilityTimelineController@index | facility-timeline | なし |
| GET | /appointment | AppointmentController@index | appointment.index | なし |
| POST | /appointment/check-in-qr | AppointmentController@checkInQr | appointment.check-in-qr | なし |
| POST | /appointment/check-in-number | AppointmentController@checkInNumber | appointment.check-in-number | なし |
| GET | /delivery-pickup/select | DeliveryPickupController@select | delivery-pickup.select | なし |
| GET | /interview | InterviewController@index | interview.index | なし |
| POST | /interview/notify-staff | InterviewController@notifyStaff | interview.notify-staff | なし |
| GET | /other-visitor/create | OtherVisitorController@create | other-visitor.create | なし |
| POST | /other-visitor/select-department | OtherVisitorController@selectDepartment | other-visitor.select-department | なし |
| POST | /other-visitor/store | OtherVisitorController@store | other-visitor.store | なし |
| GET | /visitor/scan-qr | VisitorController@scanQr | visitor.scan-qr | なし |
| POST | /visitor/check-in | VisitorController@checkIn | visitor.check-in | なし |
| GET | /visitor/create | VisitorController@create | visitor.create | なし |
| POST | /visitor/store | VisitorController@store | visitor.store | なし |
| GET | /visitor/complete | VisitorController@complete | visitor.complete | なし |
| GET | /delivery/create | DeliveryController@create | delivery.create | なし |
| GET | /delivery/capture | DeliveryController@capture | delivery.capture | なし |
| POST | /delivery/store | DeliveryController@store | delivery.store | なし |
| GET | /delivery/{delivery}/qr | DeliveryController@qrCode | delivery.qr | なし |
| POST | /delivery/{delivery}/print | DeliveryController@print | delivery.print | なし |
| GET | /delivery/{delivery} | DeliveryController@show | delivery.show | なし |
| GET | /pickup/create | PickupController@create | pickup.create | なし |
| POST | /pickup/store | PickupController@store | pickup.store | なし |
| GET | /pickup/{pickup}/qr | PickupController@qrCode | pickup.qr | なし |
| POST | /pickup/{pickup}/print | PickupController@print | pickup.print | なし |
| GET | /pickup/{pickup} | PickupController@show | pickup.show | なし |
| GET | /twilio-test | TwilioTestController@index | twilio-test.index | なし |
| POST | /twilio-test/make-call | TwilioTestController@makeCall | twilio-test.make-call | なし |
| POST | /twilio-test/check-status | TwilioTestController@checkCallStatus | twilio-test.check-status | なし |
| POST | /twilio-test/send-sms | TwilioTestController@sendSms | twilio-test.send-sms | なし |
| GET | /twilio-voice | TwilioVoiceController@index | twilio-voice.index | なし |
| POST | /twilio-voice/token | TwilioVoiceController@generateToken | twilio-voice.token | なし |
| POST | /twilio-voice/outgoing | TwilioVoiceController@handleOutgoingCall | twilio-voice.outgoing | なし |
| POST | /twilio-voice/incoming | TwilioVoiceController@handleIncomingCall | twilio-voice.incoming | なし |
| POST | /twilio-voice/status | TwilioVoiceController@callStatusCallback | twilio-voice.status | なし |
| GET | /twilio-voice/test | TwilioVoiceController@testDevice | twilio-voice.test | なし |
| GET | /test | TestController@test | test | なし（※デバッグ用・dd()） |

---

## web.php（管理系 `/admin/*`・`localstorage.auth`）

| メソッド | URI | コントローラ@アクション | ルート名 |
|---------|-----|----------------------|---------|
| GET | /admin/dashboard | Admin\DashboardController@index | admin.dashboard |
| resource | /admin/appointments | Admin\AppointmentController | admin.appointments.* |
| resource(except show) | /admin/facilities | Admin\FacilityController | admin.facilities.* |
| resource | /admin/facility-reservations | Admin\FacilityReservationController | admin.facility-reservations.* |
| resource | /admin/project-groups | Admin\ProjectGroupController | admin.project-groups.* |
| GET | /admin/facilities/{facility}/schedule | Admin\AppointmentController@getFacilitySchedule | admin.facilities.schedule |
| GET | /admin/groups/{group}/users | Admin\AppointmentController@getUsersByGroup | admin.groups.users |
| POST | /admin/user-schedules | Admin\AppointmentController@getUserSchedules | admin.user-schedules.get |
| resource | /admin/staff-members | Admin\StaffMemberController | admin.staff-members.* |
| POST | /admin/notification-settings/test-send | NotificationSettingController@sendTest | admin.notification-settings.test-send |
| POST | /admin/notification-settings/{notification_setting}/toggle | NotificationSettingController@toggle | admin.notification-settings.toggle |
| resource | /admin/notification-settings | NotificationSettingController | admin.notification-settings.* |
| resource | /admin/announcements | Admin\AnnouncementController | admin.announcements.* |
| GET | /admin/deliveries | DeliveryController@adminIndex | admin.deliveries.index |
| GET | /admin/deliveries/{delivery} | DeliveryController@adminShow | admin.deliveries.show |
| POST | /admin/deliveries/{delivery}/apply-seal | DeliveryController@applyDigitalSeal | admin.deliveries.apply-seal |
| POST | /admin/deliveries/{delivery}/rotate-image | DeliveryController@rotateImage | admin.deliveries.rotate-image |
| POST | /admin/deliveries/{delivery}/link-order | DeliveryController@linkOrder | admin.deliveries.link-order |
| POST | /admin/deliveries/{delivery}/unlink-order | DeliveryController@unlinkOrder | admin.deliveries.unlink-order |
| GET | /admin/pickups | PickupController@adminIndex | admin.pickups.index |
| GET | /admin/pickups/{pickup} | PickupController@adminShow | admin.pickups.show |
| POST | /admin/pickups/{pickup}/apply-seal | PickupController@applyDigitalSeal | admin.pickups.apply-seal |
| POST | /admin/pickups/{pickup}/rotate-image | PickupController@rotateImage | admin.pickups.rotate-image |

**`Route::resource` 展開**: index(GET) / create(GET) / store(POST) / show(GET ※facilitiesは除外) / edit(GET) / update(PUT,PATCH) / destroy(DELETE)。

---

## api.php（`/api` プレフィックス）

| メソッド | URI | コントローラ@アクション | 認証 |
|---------|-----|----------------------|------|
| GET | /api/user | クロージャ（$request->user()） | auth:sanctum |
| GET | /api/users | Api\UserController@index | api |
| GET | /api/users/{userId} | Api\UserController@show | api |
| POST | /api/login-local | Auth\LocalStorageAuthController@login | api |
| POST | /api/logout-local | Auth\LocalStorageAuthController@logout | api |
| POST | /api/test-password | Auth\LocalStorageAuthController@testPassword | api（※デバッグ用・平文露出） |
| POST | /api/set-session-user | Auth\LocalStorageAuthController@setSessionUser | api |
| GET | /api/initial-orders | ReceiveController@getInitialOrders | api |
| GET | /api/com-names | ReceiveController@getComNames | api |

> **フロントとの不整合**: `resources/js/utils/auth.js` は `/auth/login-local`・`/auth/logout-local`・`/auth/verify` を呼び出すが、実ルートは `/api/login-local`・`/api/logout-local`（`verify` 相当はルート未登録）。`/auth/*` プレフィックスのルートは現行ファイルに存在しない。要確認。

---

## auth.php（Laravel Breeze 標準）

| メソッド | URI | コントローラ@アクション | ルート名 | 認証 |
|---------|-----|----------------------|---------|------|
| GET | /register | RegisteredUserController@create | register | guest |
| POST | /register | RegisteredUserController@store | - | guest |
| GET | /login | AuthenticatedSessionController@create | login | guest |
| POST | /login | AuthenticatedSessionController@store | - | guest |
| GET | /forgot-password | PasswordResetLinkController@create | password.request | guest |
| POST | /forgot-password | PasswordResetLinkController@store | password.email | guest |
| GET | /reset-password/{token} | NewPasswordController@create | password.reset | guest |
| POST | /reset-password | NewPasswordController@store | password.store | guest |
| POST | /logout | AuthenticatedSessionController@destroy | logout | auth |
| POST | /api/logout | AuthenticatedSessionController@apiLogout | api.logout | auth |
| GET | /verify-email | EmailVerificationPromptController | verification.notice | auth |
| GET | /verify-email/{id}/{hash} | VerifyEmailController | verification.verify | auth, signed, throttle:6,1 |
| POST | /email/verification-notification | EmailVerificationNotificationController@store | verification.send | auth, throttle:6,1 |
| GET | /confirm-password | ConfirmablePasswordController@show | password.confirm | auth |
| POST | /confirm-password | ConfirmablePasswordController@store | - | auth |
| PUT | /password | PasswordController@update | password.update | auth |

---

## 未接続のコントローラ・メソッド（参考）

現行ルートファイルに紐付いていない（デッドコード/将来用）:

- `Api\AuthController` — 空スタブ
- `Auth\UserListController@index` — ログイン画面ユーザードロップダウン用JSON（ルート未接続）
- `Auth\LocalStorageAuthController::verify` — メソッドはあるがルート未登録
- `ProfileController`（edit/update/destroy）— 今回のルートファイルに未接続（Breezeのプロフィール画面用）
