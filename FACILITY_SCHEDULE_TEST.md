# 施設予約カレンダーのテスト手順

## 問題の解決方法

施設を選択してもカレンダーに予定が表示されない場合、以下の手順で確認・修正してください。

## 1. テストデータの作成

まず、テスト用の施設予約データを作成します：

```bash
php artisan db:seed --class=FacilityScheduleTestSeeder
```

このシーダーは以下を作成します：
- 3つの施設（会議室A、B、C）※既に存在する場合はスキップ
- 各施設に3〜5件の予約（今日から7日間の範囲）
- 各予約に参加者を紐付け

## 2. データベースの確認

データが正しく作成されたか確認：

```bash
# 施設を確認
php artisan tinker
>>> App\Models\Facility::all();

# 予定を確認
>>> App\Models\ScheduleEvent::with('facility')->get();
```

## 3. APIエンドポイントのテスト

ブラウザまたはcURLでAPIをテスト：

```bash
# 施設ID 1 の予定を取得（日付は適宜変更）
curl "http://localhost/admin/facilities/1/schedule?start_date=2025-11-06&end_date=2025-11-13"
```

期待される応答：
```json
{
  "schedules": [
    {
      "id": 1,
      "facility_id": 1,
      "date": "2025-11-07",
      "title": "営業戦略ミーティング",
      "start_datetime": "2025-11-07 10:00:00",
      "end_datetime": "2025-11-07 12:00:00",
      "badge": "重要"
    }
  ],
  "facility": {
    "id": 1,
    "name": "会議室A"
  },
  "date_range": {
    "start": "2025-11-06",
    "end": "2025-11-13"
  }
}
```

## 4. フロントエンドでの確認

1. ブラウザの開発者ツールを開く（F12）
2. Consoleタブを開く
3. アポイント登録画面に移動
4. ステップ2（施設予約）に進む
5. 施設を選択
6. コンソールに以下のログが表示されることを確認：
   ```
   Loading schedules for facility: 1
   Date range: 2025-11-06 to 2025-11-13
   API Response: {...}
   Schedules loaded: [...]
   Schedule count: 3
   ```

## 5. カレンダーの動作確認

- ✅ 施設選択後、カレンダーが表示される
- ✅ 既存の予定が赤色で表示される
- ✅ 予定にマウスオーバーすると詳細が見える
- ✅ 空いている時間帯をドラッグで選択できる
- ✅ 既存予定と重複する時間を選択するとアラートが表示される
- ✅ カレンダー下部に「この期間の予定: X件」と表示される

## トラブルシューティング

### 予定が表示されない場合

1. **ログの確認**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   施設選択時に以下のログが出力されるはず：
   ```
   [INFO] Fetching schedules: facility_id=1, start_date=..., end_date=...
   [INFO] Schedules found: count=3
   ```

2. **データベース接続の確認**
   `schedule_events`テーブルは`akioka_db`接続を使用しています。
   `.env`ファイルで`DB_CONNECTION_AKIOKA`が正しく設定されているか確認。

3. **CORS/認証エラー**
   ブラウザのコンソールでネットワークエラーがないか確認。
   認証ミドルウェアが正しく動作しているか確認。

4. **日付範囲の確認**
   作成した予定が表示期間（今日から7日間）に含まれているか確認。

### 既存データのクリア

テストデータをクリアして再作成する場合：

```bash
# 予定を全削除
php artisan tinker
>>> App\Models\ScheduleEvent::truncate();
>>> DB::connection('akioka_db')->table('schedule_participants')->truncate();

# 再度シーダーを実行
php artisan db:seed --class=FacilityScheduleTestSeeder
```

## カレンダーの機能

### 表示機能
- 📅 今日から7日間のカレンダー
- ⏰ 8:00〜20:00の時間軸
- 🔴 既存予定の可視化（赤色）
- 🔵 本日の日付をハイライト

### 選択機能
- 🖱️ マウスドラッグで時間帯を選択
- 🟢 選択中の時間帯を緑色で表示
- ⚠️ 重複チェック（既存予定と重複不可）
- ✅ 選択内容の確認表示

### データ連携
- リアルタイムでAPIから予定を取得
- ローディング状態の表示
- エラーハンドリング
- 予定数の表示

