<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 格納先（棚番地マスタ・生産管理系DBと共有するテーブル）
 */
class StorageAddress extends Model
{
    protected $table = 'storage_addresses';

    /**
     * 画面表示用のラベル（例: "A-5-2（棚A）"）
     * address だけでは一意にならないため棚(shelf)を併記する。
     */
    public function getLabelAttribute(): string
    {
        $label = $this->address ?: ('#' . $this->id);
        if (!empty($this->shelf)) {
            $label .= '（棚' . $this->shelf . '）';
        }
        return $label;
    }
}
