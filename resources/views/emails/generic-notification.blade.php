<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: 'Hiragino Sans','Yu Gothic',Meiryo,sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <!-- ヘッダー -->
                    <tr>
                        <td style="background:linear-gradient(90deg,#2563eb,#1d4ed8); padding:20px 28px;">
                            <span style="color:#ffffff; font-size:18px; font-weight:bold;">🏢 受付管理システム</span>
                        </td>
                    </tr>
                    <!-- 本文 -->
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 16px; font-size:18px; color:#1e293b;">{{ $subjectLine }}</h1>
                            <div style="font-size:14px; line-height:1.8; color:#334155; white-space:pre-wrap;">{{ $bodyText }}</div>
                        </td>
                    </tr>
                    <!-- フッター -->
                    <tr>
                        <td style="padding:16px 28px; border-top:1px solid #e2e8f0; background-color:#f8fafc;">
                            <span style="font-size:12px; color:#94a3b8;">本メールは受付管理システムから自動送信されています。</span>
                        </td>
                    </tr>
                </table>
                <p style="margin:16px 0 0; font-size:11px; color:#94a3b8;">© 株式会社アキオカ 受付管理システム</p>
            </td>
        </tr>
    </table>
</body>
</html>
