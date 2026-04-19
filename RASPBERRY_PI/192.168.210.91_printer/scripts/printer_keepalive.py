#!/usr/bin/env python3
import usb.core
import usb.util
import time

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
        print("✅ keep-alive 送信完了")

        # 🔽 ここでデバイスを明示的に解放（重要）
        usb.util.dispose_resources(dev)
        return True

    except Exception as e:
        print(f"❌ エラー: {e}")
        return False


if __name__ == "__main__":
    print("🖨️ Brother PT-P750W keep-alive サービスを起動中...")
    while True:
        send_keepalive()
        time.sleep(240)
