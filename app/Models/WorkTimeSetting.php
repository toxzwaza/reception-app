<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkTimeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'work_start_time',
        'work_end_time',
    ];

    /**
     * 現在有効な勤務時間設定（標準勤務）を取得
     */
    public static function current(): ?self
    {
        return static::orderBy('id')->first();
    }
}
