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
        'appointment_id',
        'date',
        'title',
        'organizer_name',   // 主催者名（Outlook）
        'organizer_email',  // 主催者メール（通知先）
        'attendee_emails',  // 参加者メール配列（通知先。会議室リソースは除外）
        'start_datetime',
        'end_datetime',
        'badge',
        'description_url',
        'status',
        'outlook_event_id',
    ];

    // dateカラムは文字列として扱い、タイムゾーン変換を避ける（date は cast しない）
    protected $casts = [
        'attendee_emails' => 'array',
    ];

    /**
     * この予定が属する施設を取得
     */
    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * この予定に紐づくアポイントを取得
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
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
