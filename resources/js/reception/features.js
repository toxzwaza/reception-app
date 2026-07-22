// 受付トップの導線カード定義（管理画面のレイアウトエディタと受付トップで共有）
// key は ScreenPattern::FEATURES と一致させること。

// 12列グリッドの共通定数
export const GRID_COLS = 12;
export const GRID_ROW_HEIGHT = 96; // 1行あたりの高さ(px)
export const GRID_MARGIN = 20; // カード間の余白(px)
export const DEFAULT_W = 6; // 既定カード幅（半分）
export const DEFAULT_H = 2; // 既定カード高（2行）

// href は route() を使うため、遅延評価できるよう関数で保持する
export const FEATURE_CARDS = [
  {
    key: "appointment_today",
    title: "アポイントありの方",
    desc: "本日ご予約の方はこちら",
    routeName: "appointment.today",
    icon: "M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z",
    iconBg: "bg-blue-50",
    iconText: "text-blue-600",
    ring: "ring-blue-100",
    blob: "from-blue-200/70 to-transparent",
    btn: "bg-blue-600 group-hover:bg-blue-700",
  },
  {
    key: "other_visitor",
    title: "アポイントなしの方",
    desc: "初めてお越しの方はこちら",
    routeName: "other-visitor.create",
    icon: "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
    iconBg: "bg-sky-50",
    iconText: "text-sky-600",
    ring: "ring-sky-100",
    blob: "from-sky-200/70 to-transparent",
    btn: "bg-sky-600 group-hover:bg-sky-700",
  },
  {
    key: "delivery_pickup",
    title: "納品・集荷の方",
    desc: "納品書・集荷伝票の処理",
    routeName: "delivery-pickup.select",
    icon: "M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4",
    iconBg: "bg-amber-50",
    iconText: "text-amber-600",
    ring: "ring-amber-100",
    blob: "from-amber-200/70 to-transparent",
    btn: "bg-amber-500 group-hover:bg-amber-600",
  },
  {
    key: "interview",
    title: "面接の方",
    desc: "面接にお越しの方はこちら",
    routeName: "interview.index",
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    iconBg: "bg-emerald-50",
    iconText: "text-emerald-600",
    ring: "ring-emerald-100",
    blob: "from-emerald-200/70 to-transparent",
    btn: "bg-emerald-600 group-hover:bg-emerald-700",
  },
  {
    key: "department_call",
    title: "担当部署を呼ぶ",
    desc: "ご用件が上記にない方・業者／配達の方",
    routeName: "department-call.select",
    icon: "M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z",
    iconBg: "bg-indigo-50",
    iconText: "text-indigo-600",
    ring: "ring-indigo-100",
    blob: "from-indigo-200/70 to-transparent",
    btn: "bg-indigo-600 group-hover:bg-indigo-700",
  },
  {
    key: "taxi",
    title: "タクシーを呼ぶ",
    desc: "タクシーを手配します",
    routeName: "taxi-call",
    icon: "M8 7h8m-9 4h10a1 1 0 011 1v4a1 1 0 01-1 1h-1a2 2 0 11-4 0H9a2 2 0 11-4 0H4a1 1 0 01-1-1v-4a1 1 0 011-1zm1-4l1.2-3.2a1 1 0 01.94-.8h3.72a1 1 0 01.94.8L18 7",
    iconBg: "bg-amber-50",
    iconText: "text-amber-600",
    ring: "ring-amber-100",
    blob: "from-amber-200/70 to-transparent",
    btn: "bg-amber-500 group-hover:bg-amber-600",
  },
  {
    // 旧アポイントあり（QR/受付番号）。既定では非表示。将来利用のため温存し、
    // 画面パターンで明示的に含めた場合のみ表示される。
    key: "appointment",
    title: "アポイントあり（QR/受付番号）",
    desc: "QR・受付番号でチェックイン",
    routeName: "appointment.index",
    defaultHidden: true,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    iconBg: "bg-slate-50",
    iconText: "text-slate-600",
    ring: "ring-slate-100",
    blob: "from-slate-200/70 to-transparent",
    btn: "bg-slate-600 group-hover:bg-slate-700",
  },
];

// キー → カード定義
export const FEATURE_CARD_MAP = Object.fromEntries(FEATURE_CARDS.map((c) => [c.key, c]));

// 既定レイアウト（2列並び）を生成。enabledKeys の順序で配置する。
export function defaultLayout(enabledKeys) {
  return enabledKeys.map((key, i) => ({
    i: key,
    x: (i % 2) * DEFAULT_W,
    y: Math.floor(i / 2) * DEFAULT_H,
    w: DEFAULT_W,
    h: DEFAULT_H,
  }));
}

// FEATURE_CARDS の定義順で並べたキー配列
export const FEATURE_ORDER = FEATURE_CARDS.map((c) => c.key);
