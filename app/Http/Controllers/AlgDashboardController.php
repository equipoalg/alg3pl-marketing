<?php

namespace App\Http\Controllers;

use App\Support\DashboardMockData;
use Illuminate\Http\Request;

class AlgDashboardController extends Controller
{
    public function show(Request $request)
    {
        // Variant: query wins (and persists), then session, then 'a'.
        $qVariant = $request->query('variant');
        if (in_array($qVariant, ['a', 'b'], true)) {
            session(['admin_variant' => $qVariant]);
            $variant = $qVariant;
        } else {
            $variant = session('admin_variant', 'a');
        }

        $chartType = in_array($request->query('chart'), ['line', 'area', 'bars'], true) ? $request->query('chart') : 'line';
        $timeRange = in_array($request->query('range'), ['7d', '30d', '90d', 'ytd'], true) ? $request->query('range') : '30d';

        return view('alg-dashboard.index', [
            'data'      => DashboardMockData::all(),
            'variant'   => $variant,
            'chartType' => $chartType,
            'timeRange' => $timeRange,
        ]);
    }
}
