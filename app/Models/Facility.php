<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    use HasFactory;

    /**
     * 使用するデータベース接続名
     *
     * @var string
     */
    protected $connection = 'akioka_db';

    protected $fillable = [
        'name',
    ];

    /**
     * この施設の予定を取得
     */
    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class);
    }
}

