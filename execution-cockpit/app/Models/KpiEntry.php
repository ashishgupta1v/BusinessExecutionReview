<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiEntry extends Model
{
    use BelongsToWorkspace;

    protected $fillable = [
        'kpi_id', 'workspace_id', 'iso_year', 'iso_week',
        'target', 'actual', 'variance', 'remarks',
    ];

    protected $casts = [
        'iso_year' => 'integer',
        'iso_week' => 'integer',
        'target'   => 'decimal:2',
        'actual'   => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Keep variance consistent with target/actual on write.
        static::saving(function (KpiEntry $entry) {
            if ($entry->actual !== null && $entry->target !== null) {
                $entry->variance = $entry->actual - $entry->target;
            }
        });
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(Kpi::class);
    }
}
