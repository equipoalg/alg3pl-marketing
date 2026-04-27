<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Support\DashboardData;
use Illuminate\Http\Request;

class AlgDashboardController extends Controller
{
    public function show(Request $request)
    {
        // Variant: query wins (and persists), then session, then user's saved preference, then 'b' (new default).
        $qVariant = $request->query('variant');
        if (in_array($qVariant, ['a', 'b'], true)) {
            session(['admin_variant' => $qVariant]);
            $variant = $qVariant;
        } else {
            $userPref = $request->user()?->preferences['variant'] ?? null;
            $variant = session('admin_variant', $userPref ?? 'b');
        }

        $chartType = in_array($request->query('chart'), ['line', 'area', 'bars'], true) ? $request->query('chart') : 'line';
        $timeRange = in_array($request->query('range'), ['7d', '30d', '90d', 'ytd'], true) ? $request->query('range') : '30d';

        // Country filter from session (set via WorkspaceController). Null = all countries.
        $countryId = session('country_filter') ? (int) session('country_filter') : null;

        return view('alg-dashboard.index', [
            'data'      => DashboardData::all($countryId, $timeRange),
            'variant'   => $variant,
            'chartType' => $chartType,
            'timeRange' => $timeRange,
        ]);
    }
}
