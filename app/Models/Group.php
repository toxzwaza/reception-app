<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;



    protected $fillable = [
        'name',
        'phone_number', // アポなし来訪時に部署代表電話へTwilioでつなぐための番号
    ];

    /**
     * この部署に属するユーザーを取得
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
