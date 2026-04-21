<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentMetric;
use App\Models\SalesMetric;
use App\Models\SustainabilityMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetricsApiController extends Controller
{
    public function shipments(Request $request): JsonResponse
    {
        $data = ShipmentMetric::query()
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->period_type, fn ($q, $v) => $q->where('period_type', $v))
            ->when($request->from, fn ($q, $v) => $q->where('period_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->where('period_date', '<=', $v))
            ->orderByDesc('period_date')
            ->paginate($request->per_page ?? 50);

        return response()->json($data);
    }

    public function sales(Request $request): JsonResponse
    {
        $data = SalesMetric::query()
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->from, fn ($q, $v) => $q->where('period_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->where('period_date', '<=', $v))
            ->orderByDesc('period_date')
            ->paginate($request->per_page ?? 50);

        return response()->json($data);
    }

    public function sustainability(Request $request): JsonResponse
    {
        $data = SustainabilityMetric::query()
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->from, fn ($q, $v) => $q->where('period_date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->where('period_date', '<=', $v))
            ->orderByDesc('period_date')
            ->paginate($request->per_page ?? 50);

        return response()->json($data);
    }

    public function storeShipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'period_date' => 'required|date',
            'period_type' => 'required|in:daily,weekly,monthly',
            'total_shipments' => 'integer|min:0',
            'on_time_shipments' => 'integer|min:0',
            'in_full_shipments' => 'integer|min:0',
            'otif_shipments' => 'integer|min:0',
            'total_revenue' => 'numeric|min:0',
            'total_cost' => 'numeric|min:0',
            'total_weight_kg' => 'numeric|min:0',
            'total_cbm' => 'numeric|min:0',
            'total_teus' => 'integer|min:0',
        ]);

        // Calculate derived fields
        if (isset($validated['total_shipments']) && $validated['total_shipments'] > 0) {
            $validated['otif_percentage'] = round(($validated['otif_shipments'] ?? 0) / $validated['total_shipments'] * 100, 2);
            $validated['cost_to_serve'] = round(($validated['total_cost'] ?? 0) / $validated['total_shipments'], 2);
        }
        if (($validated['total_revenue'] ?? 0) > 0) {
            $validated['gross_margin'] = round((($validated['total_revenue'] - ($validated['total_cost'] ?? 0)) / $validated['total_revenue']) * 100, 2);
        }

        $metric = ShipmentMetric::updateOrCreate(
            [
                'tenant_id' => app('current_tenant_id'),
                'country_id' => $validated['country_id'],
                'period_date' => $validated['period_date'],
                'period_type' => $validated['period_type'],
            ],
            $validated
        );

        return response()->json($metric, 201);
    }
}
