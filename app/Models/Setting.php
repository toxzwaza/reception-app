<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 汎用キー/バリュー設定。画面切替パスワード（ハッシュ）などを保存する。
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** 画面切替パスワードの保存キー */
    public const SCREEN_SWITCH_PASSWORD = 'screen_switch_password';

    /** 設定値を取得（未設定なら $default） */
    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /** 設定値を保存（存在すれば更新、なければ作成） */
    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
