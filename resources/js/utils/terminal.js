/**
 * 受付端末番号ヘルパー（受付横断連携）
 *
 * タブレットで一度だけ「/?terminal=番号」を開くと、その番号を localStorage に保存する。
 * 受付の各リクエストに terminal として付け、サーバー側で全画面にするカメラを決める。
 */

const TERMINAL_KEY = 'reception_terminal';

const parseTerminal = (value) => {
    if (value === null || value === undefined || !/^\d{1,2}$/.test(String(value))) {
        return null;
    }
    const number = parseInt(value, 10);
    return number >= 1 && number <= 99 ? number : null;
};

/**
 * URL の ?terminal=N を localStorage に保存する（1〜99 のみ）
 */
export const captureTerminalFromUrl = () => {
    try {
        const number = parseTerminal(new URLSearchParams(window.location.search).get('terminal'));
        if (number !== null) {
            localStorage.setItem(TERMINAL_KEY, number.toString());
        }
    } catch (e) {
        // localStorage が使えない環境でも受付は止めない
    }
};

/**
 * 保存済みの端末番号（未設定なら null）
 */
export const getTerminal = () => {
    try {
        return parseTerminal(localStorage.getItem(TERMINAL_KEY));
    } catch (e) {
        return null;
    }
};
