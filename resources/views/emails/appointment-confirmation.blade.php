<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>アポイント登録確認</title>
    <style>
        body {
            font-family: 'Hiragino Kaku Gothic ProN', 'ヒラギノ角ゴ ProN W3', 'Meiryo', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .reception-info {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .reception-number {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            margin: 10px 0;
        }
        .qr-code {
            margin: 20px 0;
            text-align: center;
        }
        .details {
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-label {
            font-weight: bold;
            width: 120px;
            flex-shrink: 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>事前アポイント登録完了</h1>
        <p>いつもお世話になっております。</p>
    </div>

    <div class="content">
        <p>{{ $appointment->visitor_name }} 様</p>
        
        <p>事前アポイントの登録が完了いたしました。<br>
        当日は下記の受付番号をご提示ください。</p>

        <div class="reception-info">
            <h3>受付番号</h3>
            <div class="reception-number">{{ $appointment->reception_number }}</div>
            <p>※受付タブレットにこちらの番号を入力ください</p>
        </div>

        <div class="qr-code">
            <h3>QRコード</h3>
            <p>スマートフォンでQRコードを読み取ってチェックインできます</p>
            @if($appointment->qr_code)
                <div style="text-align: center; margin: 20px 0; background-color: #ffffff; padding: 10px; border: 1px solid #e0e0e0; border-radius: 8px; display: inline-block;">
                    {!! file_get_contents(storage_path('app/public/' . $appointment->qr_code)) !!}
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">受付番号: {{ $appointment->reception_number }}</p>
                </div>
            @else
                <div style="width: 200px; height: 200px; background-color: #f0f0f0; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;">
                    QRコード生成エラー<br>({{ $appointment->reception_number }})
                </div>
            @endif
        </div>

        <div class="details">
            <h3>登録内容</h3>
            <div class="detail-row">
                <div class="detail-label">会社名:</div>
                <div>{{ $appointment->company_name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">訪問者名:</div>
                <div>{{ $appointment->visitor_name }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">担当者:</div>
                <div>{{ $appointment->staffMember->name ?? '未設定' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">訪問予定日:</div>
                <div>{{ $appointment->visit_date->format('Y年m月d日') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">訪問予定時刻:</div>
                <div>{{ $appointment->visit_time->format('H:i') }}</div>
            </div>
            @if($appointment->purpose)
            <div class="detail-row">
                <div class="detail-label">訪問目的:</div>
                <div>{{ $appointment->purpose }}</div>
            </div>
            @endif
        </div>

        <div style="margin-top: 30px; padding: 20px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
            <h4>ご注意事項</h4>
            <ul>
                <li>受付番号またはQRコードを忘れずにお持ちください</li>
                <li>変更やキャンセルがございます場合は、事前にご連絡ください</li>
                <li>受付時間は訪問予定時刻の15分前からとなっております</li>
            </ul>
        </div>
    </div>

    <div class="footer">
        <p>本メールは自動送信されています。<br>
        ご不明な点がございましたら、お気軽にお問い合わせください。</p>
        <p>&copy; {{ date('Y') }} 株式会社サンプル</p>
    </div>
</body>
</html>
