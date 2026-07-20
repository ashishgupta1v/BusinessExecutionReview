<?php

namespace App\Support;

/**
 * Request-scoped holder of the active workspace id.
 * Bound as a singleton so the global scope and models can read it anywhere.
 */
class CurrentWorkspace
{
    protected ?int $id = null;

    public function set(?int $workspaceId): void
    {
        $this->id = $workspaceId;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function check(): bool
    {
        return $this->id !== null;
    }
}
