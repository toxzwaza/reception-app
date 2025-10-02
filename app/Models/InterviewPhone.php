<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewPhone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_name',
        'contact_person',
        'phone_number',
        'extension_number',
        'notes',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 有効な電話番号のみを取得
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 表示順でソート
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
