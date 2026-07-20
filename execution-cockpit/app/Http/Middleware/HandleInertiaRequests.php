<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'streak' => ['current' => $this->currentStreak($request)],
        ];
    }

    /** Current DCR streak for the sidebar shell (0 when unauthenticated). */
    private function currentStreak(Request $request): int
    {
        $user = $request->user();
        if (! $user || ! $user->current_workspace_id) {
            return 0;
        }
        $dates = \App\Models\DcrEntry::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('workspace_id', $user->current_workspace_id)
            ->orderByDesc('entry_date')
            ->pluck('entry_date')
            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->toDateString())
            ->all();
        $set = array_flip($dates);
        $streak = 0; $cursor = today();
        while (isset($set[$cursor->toDateString()])) { $streak++; $cursor = $cursor->subDay(); }
        return $streak;
    }
}
