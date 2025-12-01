<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ScheduleEvent extends Model
{
    use HasFactory;
    /**
     * 使用するデータベース接続名
     *
     * @var string
     */



    protected $fillable = [
        'facility_id',
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
     * この予定が属する施設を取得
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * この予定の参加者を取得
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'schedule_participants')
            ->withTimestamps();
    }
}
