<?php

namespace App\Http\Controllers;

use App\Models\Kpi;
use App\Models\KpiEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $currentWeek = sprintf('%d-W%02d', $now->isoWeekYear, $now->isoWeek);

        $kpis = Kpi::where('active', true)->orderBy('sort_order')->get()->map(function (Kpi $k) {
            return [
                'id'        => $k->id,
                'name'      => $k->name,
                'unit'      => $k->unit,
                'direction' => $k->direction,
                'target'    => (float) $k->target_default,
                'entries'   => $k->entries()->orderBy('iso_year')->orderBy('iso_week')->get()
                    ->map(fn (KpiEntry $e) => [
                        'iso_week' => sprintf('%d-W%02d', $e->iso_year, $e->iso_week),
                        'actual'   => (float) $e->actual,
                        'target'   => (float) $e->target,
                    ]),
            ];
        });

        return Inertia::render('KpiTracker', [
            'kpis'        => $kpis,
            'currentWeek' => $currentWeek,
        ]);
    }

    public function storeEntry(Request $request)
    {
        $data = $request->validate([
            'kpi_id'   => ['required', 'exists:kpis,id'],
            'iso_week' => ['required', 'string'],
            'actual'   => ['nullable', 'numeric'],
        ]);

        $kpi = Kpi::findOrFail($data['kpi_id']); // global scope guarantees same workspace
        [$y, $w] = array_map('intval', explode('-W', $data['iso_week']));

        KpiEntry::updateOrCreate(
            ['kpi_id' => $kpi->id, 'iso_year' => $y, 'iso_week' => $w],
            ['target' => $kpi->target_default, 'actual' => $data['actual']]  // variance auto-set on saving()
        );

        return back()->with('status', 'KPI updated');
    }
}
