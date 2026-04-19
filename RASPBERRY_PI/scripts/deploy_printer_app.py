"""192.168.210.91 に修正版 app.py を適用"""
from __future__ import annotations
import time
from pathlib import Path
import paramiko

HOST = "192.168.210.91"
USER = "to-murakami"
PASSWORD = "Murakami0819"

LOCAL_FILE = Path(__file__).parent.parent / "192.168.210.91_printer" / "scripts" / "app.py"
REMOTE_PATH = "/home/to-murakami/Documents/app.py"
BACKUP_PATH = f"/home/to-murakami/Documents/app.py.bak_{time.strftime('%Y%m%d_%H%M%S')}"

assert LOCAL_FILE.exists()

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
print(f"[{time.strftime('%H:%M:%S')}] 接続中...")
c.connect(HOST, 22, USER, PASSWORD, timeout=20)
print("接続OK")


def run(cmd, timeout=20):
    _, o, e = c.exec_command(cmd, timeout=timeout)
    return o.read().decode(errors='replace'), e.read().decode(errors='replace'), o.channel.recv_exit_status()


# 1. バックアップ
print(f"\n[1/5] バックアップ: {BACKUP_PATH}")
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S cp -a {REMOTE_PATH} {BACKUP_PATH}")
print(f"  exit={rc}")

# 2. 転送
print(f"\n[2/5] 転送")
sftp = c.open_sftp()
sftp.put(str(LOCAL_FILE), "/tmp/app.py.new")
sftp.close()
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S mv /tmp/app.py.new {REMOTE_PATH}")
print(f"  exit={rc}")

# 3. 差分確認
print(f"\n[3/5] 差分確認")
out, err, rc = run(f"grep -n 'Access-Control-Allow-Private-Network' {REMOTE_PATH}")
print(f"  検出: {out.strip()}")

# 4. サービス再起動
print(f"\n[4/5] flask-app.service 再起動")
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S systemctl restart flask-app 2>&1")
print(f"  exit={rc}")

time.sleep(3)

# 5. 起動確認
print(f"\n[5/5] 起動確認")
out, err, rc = run(f"echo '{PASSWORD}' | sudo -S systemctl is-active flask-app")
print(f"  status: {out.strip()}")

out, err, rc = run(f"echo '{PASSWORD}' | sudo -S ss -tlnp 2>/dev/null | grep ':5000'")
print(f"  listen: {out.strip() or 'not listening on 5000'}")

c.close()
print("\n完了")
