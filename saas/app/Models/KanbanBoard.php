<?php

namespace App\Models;

use App\Models\Concerns\BelongsToWorkspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    use BelongsToWorkspace;

    protected $fillable = ['workspace_id', 'name'];

    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class, 'board_id')->orderBy('position');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(KanbanCard::class, 'board_id');
    }
}
