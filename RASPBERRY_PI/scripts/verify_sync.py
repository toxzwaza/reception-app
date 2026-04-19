"""ローカル版と RPi 実機版のスクリプトの差分を確認"""
from __future__ import annotations
import hashlib
import paramiko
from pathlib import Path

USER = "to-murakami"
PASSWORD = "Murakami0819"

BASE = Path(__file__).parent.parent

TARGETS = [
    ("192.168.210.90", [
        ("192.168.210.90_scanner/scripts/main.py", "/home/to-murakami/Documents/reception-scan-camera/main.py"),
        ("192.168.210.90_scanner/scripts/reception-scan-camera.service", "/etc/systemd/system/reception-scan-camera.service"),
    ]),
    ("192.168.210.91", [
        ("192.168.210.91_printer/scripts/app.py", "/home/to-murakami/Documents/app.py"),
        ("192.168.210.91_printer/scripts/printer_keepalive.py", "/home/to-murakami/Documents/printer_keepalive.py"),
        ("192.168.210.91_printer/scripts/flask-app.service", "/etc/systemd/system/flask-app.service"),
        ("192.168.210.91_printer/scripts/printer-keepalive.service", "/etc/systemd/system/printer-keepalive.service"),
    ]),
]


def md5_local(path):
    return hashlib.md5(path.read_bytes()).hexdigest()


def md5_remote(c, path):
    _, o, _ = c.exec_command(f"md5sum {path}", timeout=10)
    out = o.read().decode().strip()
    return out.split()[0] if out else None


for host, files in TARGETS:
    print(f"\n{'='*60}\n{host}\n{'='*60}")
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        c.connect(host, 22, USER, PASSWORD, timeout=15)
    except Exception as e:
        print(f"接続失敗: {e}")
        continue

    for local_rel, remote_path in files:
        local = BASE / local_rel
        if not local.exists():
            print(f"  [?] ローカル不在: {local_rel}")
            continue
        lmd5 = md5_local(local)
        rmd5 = md5_remote(c, remote_path)
        match = "一致" if lmd5 == rmd5 else "差分あり"
        sign = "OK" if lmd5 == rmd5 else "DIFF"
        print(f"  [{sign}] {local_rel}")
        print(f"    local : {lmd5}")
        print(f"    remote: {rmd5}")
        print(f"    → {match}")
    c.close()
