<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id', 'workspace_id',
        'dcr_reminder_time', 'weekly_reminder_dow', 'weekly_reminder_time',
        'monthly_reminder_dom', 'monthly_reminder_time', 'channels', 'enabled',
    ];

    protected $casts = [
        'channels' => 'array',   // e.g. ['webpush','mail']
        'enabled'  => 'boolean',
        'weekly_reminder_dow'  => 'integer',
        'monthly_reminder_dom' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
