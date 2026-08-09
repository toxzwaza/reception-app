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


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emp_no',
        'name',
        'name_kana',
        'email',
        'mobile_phone',
        'call_search_flg',
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
        'call_search_flg' => 'boolean',
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
     * メールアドレスが登録されているユーザーのみを取得（出退勤打刻の対象者）
     */
    public function scopeWithEmail($query)
    {
        return $query->whereNotNull('email')->where('email', '<>', '');
    }

    /**
     * 出退勤打刻の対象者を取得。
     * メール登録済みかつ役員グループを除く（役員は打刻せず status 画面の閲覧のみ）。
     */
    public function scopeTimeclockTarget($query)
    {
        return $query->active()
            ->withEmail()
            ->whereDoesntHave('group', fn ($groupQuery) => $groupQuery->where('name', '役員'));
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

    /**
     * このユーザーが属する部署を取得
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * このユーザーの予定を取得
     */
    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class);
    }

    /**
     * このユーザーが所属するプロジェクトグループ
     */
    public function projectGroups()
    {
        return $this->belongsToMany(ProjectGroup::class, 'project_group_user', 'user_id', 'project_group_id')
            ->withTimestamps();
    }
}
