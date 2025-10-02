<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'akioka_db';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emp_no',
        'name',
        'email',
        'password',
        'gender_flg',
        'group_id',
        'position_id',
        'process_id',
        'is_admin',
        'dispatch_flg',
        'part_flg',
        'always_order_flg',
        'duty_flg',
        'fax_folder_name',
        'del_flg',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gender_flg' => 'boolean',
        'is_admin' => 'boolean',
        'dispatch_flg' => 'boolean',
        'part_flg' => 'boolean',
        'always_order_flg' => 'boolean',
        'duty_flg' => 'boolean',
        'del_flg' => 'boolean',
    ];

    /**
     * 削除されていないユーザーのみを取得（del_flg = 0）
     */
    public function scopeActive($query)
    {
        return $query->where('del_flg', 0);
    }

    /**
     * 管理者権限を持つユーザーのみを取得
     */
    public function scopeAdmin($query)
    {
        return $query->where('is_admin', 1);
    }

    /**
     * ユーザーが管理者かどうかを判定
     */
    public function isAdmin(): bool
    {
        return $this->is_admin == 1;
    }

    /**
     * ユーザーが削除されているかどうかを判定
     */
    public function isDeleted(): bool
    {
        return $this->del_flg == 1;
    }

    /**
     * 性別を取得（日本語）
     */
    public function getGenderLabelAttribute(): string
    {
        return $this->gender_flg == 1 ? '女性' : '男性';
    }
}
