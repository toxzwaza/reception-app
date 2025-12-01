<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 使用するデータベース接続名
     *
     * @var string
     */


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
        'send_flg',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'visit_time' => 'datetime:H:i',
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'send_flg' => 'boolean',
    ];

    /**
     * 担当スタッフとのリレーション
     */
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * QRコード画像を生成してファイルパスを返す
     */
    public function generateQrCode(): string
    {
        return \App\Services\QrCodeService::generateQrCodeImage($this->reception_number);
    }

    /**
     * QRコード画像のURLを取得
     */
    public function getQrCodeUrl(): string
    {
        if ($this->qr_code) {
            return \App\Services\QrCodeService::getQrCodeUrl($this->qr_code);
        }
        return '';
    }

    /**
     * QRコード画像のBase64 data URIを取得
     */
    public function getQrCodeDataUri(): string
    {
        if ($this->qr_code) {
            return \App\Services\QrCodeService::getQrCodeDataUri($this->qr_code);
        }
        return '';
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
