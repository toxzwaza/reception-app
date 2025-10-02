/**
 * localStorage認証ヘルパー
 */

const USER_ID_KEY = 'user_id';
const USER_DATA_KEY = 'user_data';

/**
 * localStorageにuser_idを保存
 */
export const setUserId = (userId) => {
    localStorage.setItem(USER_ID_KEY, userId.toString());
};

/**
 * localStorageからuser_idを取得
 */
export const getUserId = () => {
    const userId = localStorage.getItem(USER_ID_KEY);
    return userId ? parseInt(userId, 10) : null;
};

/**
 * localStorageにユーザーデータを保存
 */
export const setUserData = (userData) => {
    localStorage.setItem(USER_DATA_KEY, JSON.stringify(userData));
};

/**
 * localStorageからユーザーデータを取得
 */
export const getUserData = () => {
    const data = localStorage.getItem(USER_DATA_KEY);
    return data ? JSON.parse(data) : null;
};

/**
 * localStorageから認証情報をクリア
 */
export const clearAuth = () => {
    localStorage.removeItem(USER_ID_KEY);
    localStorage.removeItem(USER_DATA_KEY);
};

/**
 * ログイン済みかどうかを確認
 */
export const isAuthenticated = () => {
    return getUserId() !== null;
};

/**
 * ユーザーIDを検証
 */
export const verifyUserId = async (userId) => {
    try {
        const response = await fetch('/auth/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ user_id: userId }),
        });

        const data = await response.json();
        
        if (data.valid) {
            setUserData(data.user);
            return true;
        } else {
            clearAuth();
            return false;
        }
    } catch (error) {
        console.error('ユーザー検証エラー:', error);
        clearAuth();
        return false;
    }
};

/**
 * ログイン処理
 */
export const login = async (userId, password) => {
    try {
        const response = await fetch('/auth/login-local', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                user_id: userId,
                password: password,
            }),
        });

        const data = await response.json();

        if (data.success) {
            setUserId(data.user.id);
            setUserData(data.user);
            return { success: true, user: data.user };
        } else {
            return { success: false, error: 'ログインに失敗しました' };
        }
    } catch (error) {
        console.error('ログインエラー:', error);
        if (error.response?.data?.errors) {
            return { success: false, errors: error.response.data.errors };
        }
        return { success: false, error: 'ログインに失敗しました' };
    }
};

/**
 * ログアウト処理
 */
export const logout = async () => {
    try {
        await fetch('/auth/logout-local', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
    } catch (error) {
        console.error('ログアウトエラー:', error);
    } finally {
        clearAuth();
    }
};

