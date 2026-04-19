"""
Raspberry Pi 読み取り専用解析スクリプト
- 192.168.210.90 (納品・集荷スキャン用)
- 192.168.210.91 (ラベルプリンター用)
本体に変更を加える操作は一切行わない。
"""
from __future__ import annotations
import sys, time
from pathlib import Path
import paramiko

USER = "to-murakami"
PASSWORD = "Murakami0819"

HOSTS = [
    ("192.168.210.90", "scanner", "納品・集荷スキャン用"),
    ("192.168.210.91", "printer", "ラベルプリンター用"),
]

READ_ONLY_COMMANDS: list[tuple[str, str]] = [
    # --- OS / Hardware ---
    ("OS release", "cat /etc/os-release"),
    ("Kernel", "uname -a"),
    ("Raspi model", "cat /proc/device-tree/model 2>/dev/null; echo"),
    ("Uptime", "uptime"),
    ("CPU info", "cat /proc/cpuinfo | grep -E 'Model|Hardware|Revision' | head -10"),
    ("Memory", "free -h"),
    ("Disk usage", "df -hT"),
    ("Temperature", "vcgencmd measure_temp 2>/dev/null || echo 'not available'"),

    # --- Network ---
    ("Network interfaces", "ip -br addr"),
    ("Default route", "ip route | head -5"),
    ("Hostname", "hostname; cat /etc/hostname"),
    ("Listening ports", "ss -tlnp 2>/dev/null || sudo -n ss -tlnp 2>/dev/null"),

    # --- Services ---
    ("Running services", "systemctl list-units --type=service --state=running --no-pager --no-legend | head -40"),
    ("Enabled user services", "systemctl list-unit-files --type=service --state=enabled --no-pager --no-legend | grep -v '^systemd\\|^snap\\|^cron\\|^rsyslog\\|^ssh\\|^dbus\\|^networkd\\|^resolved\\|^udev\\|^getty' | head -40"),
    ("Failed services", "systemctl list-units --type=service --state=failed --no-pager --no-legend"),

    # --- Python / Node ---
    ("Python", "python3 --version 2>&1; which python3 pip3"),
    ("Node", "node -v 2>/dev/null; npm -v 2>/dev/null"),
    ("Installed Python packages", "pip3 list 2>/dev/null | head -40; pip list 2>/dev/null | head -40"),

    # --- Application search ---
    ("Home contents", "ls -la ~ 2>/dev/null"),
    ("Home projects", "find ~ -maxdepth 3 -name '*.py' -not -path '*/\\.*' 2>/dev/null | head -30"),
    ("Home scripts/services", "find ~ -maxdepth 3 \\( -name '*.sh' -o -name '*.service' -o -name 'app.py' -o -name 'main.py' -o -name 'server.py' -o -name 'requirements.txt' -o -name 'package.json' \\) 2>/dev/null | head -30"),
    ("/opt contents", "ls -la /opt/ 2>/dev/null"),
    ("/srv contents", "ls -la /srv/ 2>/dev/null"),
    ("systemd user units", "ls -la /etc/systemd/system/*.service 2>/dev/null; systemctl list-unit-files --type=service --state=enabled --no-pager --no-legend | grep -v '^systemd\\|^snap\\|^cron\\|^rsyslog\\|^ssh\\|^dbus\\|^networkd\\|^resolved\\|^udev\\|^getty\\|^cups\\|^avahi\\|^bluetooth\\|^wpa\\|^triggerhappy'"),

    # --- USB devices (for printer/scanner) ---
    ("USB devices", "lsusb 2>/dev/null"),
    ("CUPS printers", "lpstat -p 2>/dev/null; lpstat -d 2>/dev/null"),
    ("Scanner devices", "scanimage -L 2>/dev/null || echo 'no scanimage'"),

    # --- Python services status ---
    ("Python processes", "ps aux | grep -i 'python\\|node\\|flask\\|gunicorn\\|uvicorn\\|socket' | grep -v grep | head -20"),
    ("Listening Python apps", "ss -tlnp 2>/dev/null | grep -iE 'python|node' || echo 'no matches'"),

    # --- Common config ---
    ("Cron user", "crontab -l 2>/dev/null || echo 'no user crontab'"),
    ("Cron system", "ls -la /etc/cron.d/ 2>/dev/null | grep -v placeholder"),

    # --- SSL/Network config for scanner (from reception-app .env) ---
    ("nginx/apache?", "which nginx apache2 2>/dev/null; systemctl is-active nginx apache2 2>/dev/null"),
    ("port 5001 (scanner)", "ss -tlnp 2>/dev/null | grep ':5001' || echo 'not listening on 5001'"),

    # --- Firewall ---
    ("ufw status", "sudo -n ufw status 2>/dev/null || echo 'needs sudo'"),
]


def run(c, cmd, timeout=30):
    _, o, e = c.exec_command(cmd, timeout=timeout)
    return o.read().decode(errors='replace'), e.read().decode(errors='replace'), o.channel.recv_exit_status()


def analyze_host(host: str, alias: str, desc: str):
    out_dir = Path(__file__).parent.parent / f"{host}_{alias}"
    out_path = out_dir / "保守レポート" / "20260416_初回解析.md"
    out_path.parent.mkdir(parents=True, exist_ok=True)

    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    print(f"\n{'='*60}\n[{time.strftime('%H:%M:%S')}] {host} ({desc}) に接続中...\n{'='*60}", flush=True)
    try:
        c.connect(host, 22, USER, PASSWORD, timeout=20, banner_timeout=20, auth_timeout=20)
    except Exception as e:
        print(f"接続失敗: {e}")
        return False
    print(f"接続成功", flush=True)

    lines = [
        f"# {host} Raspberry Pi 初回解析レポート",
        "",
        f"- 用途: {desc}",
        f"- 実施日時: 2026-04-16",
        f"- 実施者: to-murakami (SSH read-only)",
        f"- 注意: 読み取り専用コマンドのみ。本体設定に変更なし",
        "",
    ]

    for i, (label, cmd) in enumerate(READ_ONLY_COMMANDS, 1):
        print(f"  [{i:2d}/{len(READ_ONLY_COMMANDS)}] {label}", flush=True)
        try:
            o, e, rc = run(c, cmd)
        except Exception as ex:
            o, e, rc = "", f"EXC: {ex}", -1
        lines += [f"## {label}", "", "```bash", f"$ {cmd}"]
        if o.strip(): lines.append(o.rstrip())
        if e.strip():
            lines.append("--- stderr ---")
            lines.append(e.rstrip())
        lines += [f"(exit={rc})", "```", ""]

    c.close()
    out_path.write_text("\n".join(lines), encoding="utf-8")
    print(f"保存: {out_path}")
    return True


def main():
    for host, alias, desc in HOSTS:
        analyze_host(host, alias, desc)
    print("\n完了")


if __name__ == "__main__":
    main()
