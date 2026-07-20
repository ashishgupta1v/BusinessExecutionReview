<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kpi extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'workspace_id', 'name', 'unit', 'direction', 'target_default', 'sort_order', 'active',
    ];

    protected $casts = [
        'target_default' => 'decimal:2',
        'sort_order'     => 'integer',
        'active'         => 'boolean',
    ];

    public const DIR_HIGHER = 'higher_better';
    public const DIR_LOWER   = 'lower_better';

    public function entries(): HasMany
    {
        return $this->hasMany(KpiEntry::class);
    }
}
