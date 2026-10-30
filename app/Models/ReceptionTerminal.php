<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 受付タブレット（端末番号と全画面にするカメラの紐付け）
 */
class ReceptionTerminal extends Model
{
    protected $fillable = [
        'number',
        'name',
        'camera_src',
        'enabled',
    ];

    protected $casts = [
        'number' => 'integer',
        'enabled' => 'boolean',
    ];
}
