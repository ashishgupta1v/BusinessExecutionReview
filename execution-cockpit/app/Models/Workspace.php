<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = [
        'name', 'business_type', 'owner_user_id', 'plan', 'trial_ends_at', 'settings',
    ];

    protected $casts = [
        'settings'      => 'array',   // dcr_reminder_time, weekly_review_day, stale_task_days
        'trial_ends_at' => 'datetime',
    ];

    protected $attributes = [
        'plan'     => 'free',
        'settings' => '{"dcr_reminder_time":"18:00","weekly_review_day":5,"stale_task_days":3}',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot('role')
            ->withTimestamps();
    }
}
