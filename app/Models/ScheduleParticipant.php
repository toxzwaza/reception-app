<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleParticipant extends Model
{
    use HasFactory;
    /**
     * 使用するデータベース接続名
     *
     * @var string
     */
    protected $connection = 'akioka_db';
    protected $table = 'schedule_participants';

    protected $fillable = [
        'schedule_event_id',
        'user_id',
    ];

    /**
     * この参加者が属する予定を取得
     */
    public function scheduleEvent(): BelongsTo
    {
        return $this->belongsTo(ScheduleEvent::class);
    }

    /**
     * この参加レコードが属するユーザーを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

