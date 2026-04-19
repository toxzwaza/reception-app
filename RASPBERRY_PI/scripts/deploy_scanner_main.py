"""
192.168.210.90 に修正版 main.py を適用する
- バックアップを取る
- SCP で転送
- systemd サービス再起動
- 起動後のログを確認
"""
from __future__ import annotations
import time
from pathlib import Path
import paramiko

HOST = "192.168.210.90"
USER = "to-murakami"
PASSWORD = "Murakami0819"

LOCAL_FILE = Path(__file__).parent.parent / "192.168.210.90_scanner" / "scripts" / "main.py"
REMOTE_PATH = "/home/to-murakami/Documents/reception-scan-camera/main.py"
BACKUP_PATH = f"/home/to-murakami/Documents/reception-scan-camera/main.py.bak_{time.strftime('%Y%m%d_%H%M%S')}"

assert LOCAL_FILE.exists(), f"ローカルファイルが見つかりません: {LOCAL_FILE}"

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
print(f"[{time.strftime('%H:%M:%S')}] 接続中...")
c.connect(HOST, 22, USER, PASSWORD, timeout=20)
print("接続OK")


def run(cmd, timeout=20):
    _, o, e = c.exec_command(cmd, timeout=timeout)
    out = o.read().decode(errors='replace')
    err = e.read().decode(errors='replace')
    rc = o.channel.recv_exit_status()
    return out, err, rc


# 1. バックアップ
print(f"\n[1/5] バックアップ作成: {BACKUP_PATH}")
out, err, rc = run(f"cp -a {REMOTE_PATH} {BACKUP_PATH}")
print(f"  exit={rc}" + (f" stderr: {err}" if err.strip() else ""))

# 2. SCP で転送
print(f"\n[2/5] ファイル転送: {LOCAL_FILE} → {REMOTE_PATH}")
sftp = c.open_sftp()
sftp.put(str(LOCAL_FILE), REMOTE_PATH)
sftp.close()
print("  転送完了")

# 3. 差分確認（追加した行だけチェック）
print(f"\n[3/5] 差分確認")
out, err, rc = run(f"grep -n 'Access-Control-Allow-Private-Network' {REMOTE_PATH}")
print(f"  検出: {out.strip()}")

# 4. サービス再起動（sudo 必須 → 村上さんPWでsudo）
print(f"\n[4/5] reception-scan-camera.service 再起動")
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S systemctl restart reception-scan-camera 2>&1")
print(f"  exit={rc}")
if out.strip(): print(f"  stdout: {out.strip()}")

time.sleep(3)

# 5. 起動確認
print(f"\n[5/5] サービス状態確認")
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S systemctl is-active reception-scan-camera")
print(f"  status: {out.strip()}")

out, err, rc = run(f"ss -tlnp 2>/dev/null | grep ':5001' || echo 'not listening on 5001'")
print(f"  listen: {out.strip()}")

out, err, rc = run(f"echo '{PASSWORD}' | sudo -S journalctl -u reception-scan-camera -n 10 --no-pager")
print(f"  journal:\n{out}")

c.close()
print("\n完了")
