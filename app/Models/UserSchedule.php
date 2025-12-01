<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSchedule extends Model
{
    use HasFactory;

    /**
     * 使用するデータベース接続名
     *
     * @var string
     */


    protected $fillable = [
        'user_id',
        'date',
        'title',
        'start_datetime',
        'end_datetime',
        'badge',
        'description_url',
        'status',
    ];

    // dateカラムは文字列として扱い、タイムゾーン変換を避ける
    // protected $casts = [
    //     'date' => 'date',
    // ];

    /**
     * この予定が属するユーザーを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
