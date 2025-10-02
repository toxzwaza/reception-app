<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reception_number',
        'company_name',
        'visitor_name',
        'visitor_email',
        'visitor_phone',
        'staff_member_id',
        'visit_date',
        'visit_time',
        'purpose',
        'qr_code',
        'is_checked_in',
        'checked_in_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'visit_time' => 'datetime:H:i',
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    /**
     * 担当スタッフとのリレーション
     */
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    /**
     * QRコードデータを生成
     */
    public function generateQrCode(): string
    {
        return json_encode([
            'type' => 'appointment',
            'appointment_id' => $this->id,
            'reception_number' => $this->reception_number,
        ]);
    }

    /**
     * 受付番号を自動生成
     */
    public static function generateReceptionNumber(): string
    {
        do {
            $number = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('reception_number', $number)->exists());
        
        return $number;
    }
}
