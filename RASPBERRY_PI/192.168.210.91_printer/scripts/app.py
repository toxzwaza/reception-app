from flask import Flask, request, jsonify, render_template_string, send_from_directory, redirect
from PIL import Image, ImageDraw, ImageFont
import qrcode
from datetime import datetime
import os, json, threading, time, usb.core, usb.util, subprocess
from brother_ql.raster import BrotherQLRaster
from brother_ql.conversion import convert
from brother_ql.backends.helpers import send
from flask_cors import CORS

app = Flask(__name__)
CORS(app)


@app.after_request
def add_pna_header(response):
    # Chrome Private Network Access 対応: 公開オリジン（akioka-reception.cloud）から
    # プライベートIP（192.168.x.x）へのアクセスを許可するために必要
    response.headers["Access-Control-Allow-Private-Network"] = "true"
    return response

BASE_DIR = "/home/to-murakami/Documents"
QR_DIR = os.path.join(BASE_DIR, "qr_history")
LOG_PATH = os.path.join(BASE_DIR, "print_log.txt")
LOGO_PATH = os.path.join(BASE_DIR, "ak_logo_black.png")
CONFIG_PATH = os.path.join(BASE_DIR, "config.json")

os.makedirs(QR_DIR, exist_ok=True)

# ===============================
#  🔧 設定の読み込み / 保存
# ===============================
def load_config():
    if os.path.exists(CONFIG_PATH):
        with open(CONFIG_PATH, "r") as f:
            return json.load(f)
    else:
        default = {"label_width": "24", "cut_enabled": True, "debug_mode": True}
        save_config(default)
        return default

def save_config(cfg):
    with open(CONFIG_PATH, "w") as f:
        json.dump(cfg, f, indent=4)

config = load_config()

# ===============================
#  🖨️ Keep-Alive スレッド
# ===============================
VENDOR_ID = 0x04f9   # Brother Industries
PRODUCT_ID = 0x2062  # PT-P750W

def send_keepalive():
    """プリンターに Keep-Alive コマンド(ESC i)を送信"""
    dev = usb.core.find(idVendor=VENDOR_ID, idProduct=PRODUCT_ID)
    if dev is None:
        print("⚠️ プリンターが見つかりません")
        return False
    try:
        if dev.is_kernel_driver_active(0):
            dev.detach_kernel_driver(0)
        dev.set_configuration()
        cfg = dev.get_active_configuration()
        intf = cfg[(0, 0)]

        ep_out = next(
            (ep for ep in intf if usb.util.endpoint_direction(ep.bEndpointAddress)
             == usb.util.ENDPOINT_OUT),
            None
        )
        if ep_out is None:
            print("❌ OUT エンドポイントが見つかりません")
            return False

        ep_out.write(b'\x1b\x69')
        print("✅ Keep-Alive 送信完了")
        usb.util.dispose_resources(dev)
        return True

    except Exception as e:
        print(f"❌ Keep-Alive エラー: {e}")
        return False

def keepalive_loop():
    print("🔁 Keep-Alive スレッド開始（4分ごと）")
    while True:
        send_keepalive()
        time.sleep(240)  # 4分おきに送信

threading.Thread(target=keepalive_loop, daemon=True).start()

# ===============================
#  🧾 QR生成（ロゴ＋日付）
# ===============================
def create_qr_with_logo(url, output_path):
    qr = qrcode.QRCode(
        version=None,
        error_correction=qrcode.constants.ERROR_CORRECT_H,
        box_size=40,
        border=0
    )
    qr.add_data(url)
    qr.make(fit=True)
    qr_img = qr.make_image(fill_color="black", back_color="white").convert("RGBA")
    qr_width, qr_height = qr_img.size

    logo = Image.open(LOGO_PATH).convert("RGBA")
    bbox = logo.getbbox()
    if bbox:
        logo = logo.crop(bbox)

    logo_size = int(qr_width * 0.5)
    logo.thumbnail((logo_size, logo_size), Image.Resampling.LANCZOS)

    date_text = datetime.now().strftime("%y/%m/%d")
    try:
        font = ImageFont.truetype("/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf", 96)
    except:
        font = ImageFont.load_default()

    temp_img = Image.new("RGBA", (1, 1))
    temp_draw = ImageDraw.Draw(temp_img)
    bbox_text = temp_draw.textbbox((0, 0), date_text, font=font)
    text_w, text_h = bbox_text[2] - bbox_text[0], bbox_text[3] - bbox_text[1]

    spacing, padding = 25, 45
    panel_w = max(logo.width, text_w) + padding * 2
    panel_h = logo.height + text_h + spacing + padding * 2
    panel = Image.new("RGBA", (panel_w, panel_h), (255, 255, 255, 255))
    draw_panel = ImageDraw.Draw(panel)

    panel.alpha_composite(logo, dest=((panel_w - logo.width)//2, padding))
    draw_panel.text(
        ((panel_w - text_w)//2, padding + logo.height + spacing),
        date_text, fill="black", font=font
    )

    qr_img.alpha_composite(panel, dest=((qr_width - panel_w)//2, (qr_height - panel_h)//2))
    qr_img.save(output_path)
    print(f"✅ QR生成完了: {output_path}")

# ===============================
#  🖨️ プリンター状態 / ログ
# ===============================
def get_printer_status():
    try:
        result = subprocess.run(["lsusb"], capture_output=True, text=True)
        return result.stdout.strip()
    except Exception as e:
        return f"エラー: {e}"

def log_print(url, filename):
    with open(LOG_PATH, "a") as f:
        f.write(f"{datetime.now().strftime('%Y-%m-%d %H:%M:%S')} | {url} | {filename}\n")

# ===============================
#  🔌 Flaskルート定義
# ===============================
@app.route('/status_check')
def status_check():
    try:
        device = usb.core.find(idVendor=VENDOR_ID, idProduct=PRODUCT_ID)
        if device:
            return jsonify({"connected": True, "model": "PT-P750W", "message": "接続OK"})
        else:
            return jsonify({"connected": False, "message": "プリンターが見つかりません"})
    except Exception as e:
        return jsonify({"connected": False, "error": str(e)})

@app.route('/print', methods=['POST'])
def print_qr():
    try:
        data = request.get_json()
        if not data or "url" not in data:
            return jsonify({"status": "error", "message": "Missing parameter: url"}), 400

        url = data["url"].strip()
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"qr_{timestamp}.png"
        filepath = os.path.join(QR_DIR, filename)

        create_qr_with_logo(url, filepath)

        qlr = BrotherQLRaster('PT-P750W')
        qlr.exception_on_warning = True
        label_code = f"pt{config['label_width']}" if not str(config['label_width']).startswith("pt") else config["label_width"]

        instructions = convert(
            qlr=qlr,
            images=[filepath],
            label=label_code,
            cut=config["cut_enabled"],
            threshold=70,
            dither=False,
            rotate='0',
            compress=True,
            red=False,
        )

        send(
            instructions=instructions,
            printer_identifier='usb://0x04f9:0x2062',
            backend_identifier='pyusb',
            blocking=True
        )

        log_print(url, filename)
        return jsonify({"status": "success", "url": url, "file": filename})
    except Exception as e:
        import traceback
        print(traceback.format_exc())
        return jsonify({"status": "error", "message": str(e)}), 500

@app.route('/update_config', methods=['POST'])
def update_config():
    config["cut_enabled"] = "cut_enabled" in request.form
    config["debug_mode"] = "debug_mode" in request.form
    save_config(config)
    return redirect('/')

@app.route('/')
def manage():
    status = get_printer_status()
    log = open(LOG_PATH).read() if os.path.exists(LOG_PATH) else "ログはまだありません。"
    files = sorted(os.listdir(QR_DIR), reverse=True)
    images = [f for f in files if f.endswith(".png")][:30]

    html = """
    <!DOCTYPE html>
    <html lang="ja">
    <head>
    <meta charset="UTF-8">
    <title>QRプリント管理</title>
    <style>
    body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
         background:#f4f5f7;margin:0;padding:40px;color:#333;}
    h1{font-size:28px;margin-bottom:20px;color:#222;}
    .card{background:white;padding:20px;margin-bottom:25px;border-radius:12px;
          box-shadow:0 2px 5px rgba(0,0,0,0.08);}
    button{background:#007bff;color:#fff;border:none;padding:10px 20px;border-radius:6px;
           cursor:pointer;font-size:14px;}
    button:hover{background:#0056b3;}
    .switch{position:relative;display:inline-block;width:46px;height:24px;margin-left:8px;}
    .switch input{display:none;}
    .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;
            background:#ccc;transition:.3s;border-radius:24px;}
    .slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;
                   background:white;transition:.3s;border-radius:50%;}
    input:checked + .slider{background:#007bff;}
    input:checked + .slider:before{transform:translateX(22px);}
    .gallery{display:flex;flex-wrap:wrap;gap:10px;}
    .gallery img{width:100px;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,0.15);}
    pre{background:#f1f1f1;padding:10px;border-radius:8px;overflow:auto;}
    </style>
    </head>
    <body>
      <h1>📋 QRプリンター管理</h1>
      <div class="card">
        <h2>🖨️ プリンター状態</h2>
        <pre>{{status}}</pre>
        <form method="post" action="/test_print">
          <button>🧾 テスト印刷</button>
        </form>
      </div>

      <div class="card">
        <h2>⚙️ 設定</h2>
        <form method="post" action="/update_config">
          <label>カット有効:
            <input type="checkbox" name="cut_enabled" {% if config.cut_enabled %}checked{% endif %}>
          </label><br><br>

          <label>デバッグモード:
            <label class="switch">
              <input type="checkbox" name="debug_mode" {% if config.debug_mode %}checked{% endif %}>
              <span class="slider"></span>
            </label>
          </label><br><br>

          <button>💾 設定を保存</button>
        </form>
      </div>

      <div class="card">
        <h2>📜 印刷ログ</h2>
        <pre>{{log}}</pre>
      </div>

      <div class="card">
        <h2>🖼️ 印刷履歴</h2>
        <div class="gallery">
          {% for img in images %}
            <a href="/qr_history/{{img}}" target="_blank">
              <img src="/qr_history/{{img}}" alt="{{img}}">
            </a>
          {% endfor %}
        </div>
      </div>
    </body></html>
    """
    return render_template_string(html, status=status, log=log, config=config, images=images)

@app.route('/qr_history/<path:filename>')
def qr_history(filename):
    return send_from_directory(QR_DIR, filename)

@app.route('/test_print', methods=['POST'])
def test_print():
    try:
        # 直接print_qr関数のロジックを呼び出す
        url = "https://akioka.cloud/test"
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        filename = f"qr_{timestamp}.png"
        filepath = os.path.join(QR_DIR, filename)

        create_qr_with_logo(url, filepath)

        qlr = BrotherQLRaster('PT-P750W')
        qlr.exception_on_warning = True
        label_code = f"pt{config['label_width']}" if not str(config['label_width']).startswith("pt") else config["label_width"]

        instructions = convert(
            qlr=qlr,
            images=[filepath],
            label=label_code,
            cut=config["cut_enabled"],
            threshold=70,
            dither=False,
            rotate='0',
            compress=True,
            red=False,
        )

        send(
            instructions=instructions,
            printer_identifier='usb://0x04f9:0x2062',
            backend_identifier='pyusb',
            blocking=True
        )

        log_print(url, filename)
        print("✅ テスト印刷成功")
    except Exception as e:
        print(f"❌ テスト印刷エラー: {e}")
        import traceback
        traceback.print_exc()
    return redirect('/')

# ===============================
#  🚀 メイン実行
# ===============================
# ===============================
#  🚀 メイン実行（Flask で直接 HTTPS を提供）
# ===============================
if __name__ == '__main__':
    host = '0.0.0.0'
    port = 5000

    CERT_PATH = "/home/to-murakami/Documents/certs/fullchain_91.pem"
    KEY_PATH  = "/home/to-murakami/Documents/certs/server_91.key"

    print("🚀 Flask (HTTPS) モードで起動中。他の端末から https://192.168.210.91:5000 でアクセスできます。")

    app.run(
        host=host,
        port=port,
        debug=config.get("debug_mode", False),
        ssl_context=(CERT_PATH, KEY_PATH)
    )
