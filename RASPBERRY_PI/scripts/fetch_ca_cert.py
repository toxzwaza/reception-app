"""Akioka-Local-CA 証明書をラズパイから取得（社内配布用）"""
from __future__ import annotations
import time
from pathlib import Path
import paramiko

USER = "to-murakami"
PASSWORD = "Murakami0819"

OUT_DIR = Path(__file__).parent.parent / "certs"
OUT_DIR.mkdir(parents=True, exist_ok=True)


def run_ssh(host, cmds):
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(host, 22, USER, PASSWORD, timeout=20)
    results = {}
    for label, cmd in cmds:
        _, o, e = c.exec_command(cmd, timeout=20)
        out = o.read().decode(errors='replace')
        err = e.read().decode(errors='replace')
        rc = o.channel.recv_exit_status()
        results[label] = {"out": out, "err": err, "rc": rc}
    c.close()
    return results


# 1. CA関連ファイルを検索
print("=" * 60)
print("CA証明書の場所を検索中（192.168.210.90）")
print("=" * 60)
results = run_ssh("192.168.210.90", [
    ("fullchain.pem 内容", "cat /home/to-murakami/Documents/reception-scan-camera/certs/fullchain.pem 2>&1"),
    ("certs ディレクトリ", "ls -la /home/to-murakami/Documents/reception-scan-camera/certs/ 2>&1"),
    ("Akioka-Local-CA ファイル検索", "find / -name 'Akioka*CA*' -o -name '*akioka*ca*' 2>/dev/null | head -20"),
    ("rootCA検索", "find / -name 'rootCA*' -o -name 'ca-cert*' 2>/dev/null | head -20"),
    ("fullchain構造", "openssl crl2pkcs7 -nocrl -certfile /home/to-murakami/Documents/reception-scan-camera/certs/fullchain.pem 2>&1 | openssl pkcs7 -print_certs -noout 2>&1 | head -30"),
])

for label, r in results.items():
    print(f"\n--- {label} ---")
    if r["out"].strip():
        print(r["out"].rstrip()[:2000])
    if r["err"].strip():
        print(f"STDERR: {r['err'].rstrip()[:500]}")


# 2. プリンターラズパイにも同様の検索
print("\n" + "=" * 60)
print("CA証明書の場所を検索中（192.168.210.91）")
print("=" * 60)
results2 = run_ssh("192.168.210.91", [
    ("fullchain_91 内容", "cat /home/to-murakami/Documents/certs/fullchain_91.pem 2>&1"),
    ("certs ディレクトリ", "ls -la /home/to-murakami/Documents/certs/ 2>&1"),
    ("Akioka-Local-CA 検索", "find / -name 'Akioka*CA*' -o -name '*local-ca*' -o -name 'rootCA*' 2>/dev/null | head -20"),
    ("fullchain構造", "openssl crl2pkcs7 -nocrl -certfile /home/to-murakami/Documents/certs/fullchain_91.pem 2>&1 | openssl pkcs7 -print_certs -noout 2>&1 | head -30"),
])

for label, r in results2.items():
    print(f"\n--- {label} ---")
    if r["out"].strip():
        print(r["out"].rstrip()[:2000])
    if r["err"].strip():
        print(f"STDERR: {r['err'].rstrip()[:500]}")
