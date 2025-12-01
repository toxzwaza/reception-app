<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 使用するデータベース接続名
     *
     * @var string
     */


    protected $fillable = [
        'title',
        'content',
        'type',
        'start_date',
        'end_date',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * 有効なお知らせのみを取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today());
    }

    /**
     * 表示順でソート
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
