"""両Raspberry Piから主要スクリプト・service定義を取得"""
from __future__ import annotations
import time
from pathlib import Path
import paramiko

USER = "to-murakami"
PASSWORD = "Murakami0819"

BASE = Path(__file__).parent.parent

TARGETS = {
    "192.168.210.90": {
        "alias": "scanner",
        "files": [
            ("/home/to-murakami/Documents/reception-scan-camera/main.py", "scripts/main.py"),
            ("/home/to-murakami/Documents/reception-scan-camera/client_test.py", "scripts/client_test.py"),
            ("/etc/systemd/system/reception-scan-camera.service", "scripts/reception-scan-camera.service"),
        ],
        "extra_cmds": [
            ("requirements", "ls /home/to-murakami/Documents/reception-scan-camera/ 2>/dev/null"),
            ("service detail", "cat /etc/systemd/system/reception-scan-camera.service"),
            ("journalctl last", "journalctl -u reception-scan-camera --no-pager -n 30 2>&1"),
        ],
    },
    "192.168.210.91": {
        "alias": "printer",
        "files": [
            ("/home/to-murakami/Documents/app.py", "scripts/app.py"),
            ("/home/to-murakami/Documents/printer_keepalive.py", "scripts/printer_keepalive.py"),
            ("/etc/systemd/system/flask-app.service", "scripts/flask-app.service"),
            ("/etc/systemd/system/printer-keepalive.service", "scripts/printer-keepalive.service"),
            ("/home/to-murakami/Documents/update_cert.sh", "scripts/update_cert.sh"),
        ],
        "extra_cmds": [
            ("Documents内容", "ls -la /home/to-murakami/Documents/ 2>/dev/null"),
            ("flask service detail", "cat /etc/systemd/system/flask-app.service"),
            ("keepalive service detail", "cat /etc/systemd/system/printer-keepalive.service"),
            ("journalctl flask", "journalctl -u flask-app --no-pager -n 30 2>&1"),
            ("journalctl keepalive", "journalctl -u printer-keepalive --no-pager -n 30 2>&1"),
        ],
    },
}


def fetch_and_save(host, alias, files, extras):
    out_base = BASE / f"{host}_{alias}"
    (out_base / "scripts").mkdir(parents=True, exist_ok=True)

    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    print(f"\n[{host} ({alias})] 接続中...", flush=True)
    c.connect(host, 22, USER, PASSWORD, timeout=20)
    print("接続OK", flush=True)

    sftp = c.open_sftp()
    for src, dst in files:
        dst_path = out_base / dst
        try:
            sftp.get(src, str(dst_path))
            print(f"  取得: {src} → {dst}")
        except Exception as e:
            print(f"  失敗: {src} ({e})")
    sftp.close()

    extra_out = [f"# {host} ({alias}) 追加情報", ""]
    for label, cmd in extras:
        print(f"  実行: {label}")
        _, o, e = c.exec_command(cmd, timeout=20)
        out = o.read().decode(errors='replace')
        err = e.read().decode(errors='replace')
        extra_out += [f"## {label}", "", "```bash", f"$ {cmd}"]
        if out.strip(): extra_out.append(out.rstrip())
        if err.strip(): extra_out += ["--- stderr ---", err.rstrip()]
        extra_out += ["```", ""]
    (out_base / "保守レポート" / "20260416_追加情報.md").write_text("\n".join(extra_out), encoding="utf-8")
    c.close()


for host, cfg in TARGETS.items():
    fetch_and_save(host, cfg["alias"], cfg["files"], cfg["extra_cmds"])

print("\n完了")
