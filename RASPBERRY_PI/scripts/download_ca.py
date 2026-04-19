"""Akioka-Local-CA rootCA.crt をラズパイから取得して certs/ に保存"""
from pathlib import Path
import paramiko

USER = "to-murakami"
PASSWORD = "Murakami0819"

OUT_DIR = Path(__file__).parent.parent / "certs"
OUT_DIR.mkdir(parents=True, exist_ok=True)

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect("192.168.210.90", 22, USER, PASSWORD, timeout=20)

sftp = c.open_sftp()
targets = [
    ("/home/to-murakami/Documents/reception-scan-camera/certs/rootCA.crt", "Akioka-Local-CA.crt"),
    ("/home/to-murakami/Documents/reception-scan-camera/certs/rootCA.pem", "Akioka-Local-CA.pem"),
]
for src, dst in targets:
    dst_path = OUT_DIR / dst
    sftp.get(src, str(dst_path))
    print(f"取得: {src} → {dst_path}")

sftp.close()
c.close()

# 確認
import subprocess
for f in OUT_DIR.glob("*.crt"):
    print(f"\n--- {f.name} 内容確認 ---")
    r = subprocess.run(["openssl", "x509", "-in", str(f), "-noout", "-subject", "-issuer", "-dates"],
                       capture_output=True, text=True)
    print(r.stdout)
