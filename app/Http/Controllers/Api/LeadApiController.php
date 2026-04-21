<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Lead;
use App\Services\Lead\LeadScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leads = Lead::query()
            ->when($request->country_id, fn ($q, $v) => $q->where('country_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->source, fn ($q, $v) => $q->where('source', $v))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 25);

        return response()->json($leads);
    }

    public function store(Request $request, LeadScoringService $scoring): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'source' => 'nullable|string|max:50',
            'landing_page' => 'nullable|string|max:500',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'service_interest' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'new';
        $lead = Lead::create($validated);
        $lead = $scoring->recalculate($lead);

        AuditLog::record('api:lead_created', $lead);

        return response()->json($lead, 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['country', 'activities']);
        return response()->json($lead);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $old = $lead->toArray();
        $lead->update($request->only([
            'name', 'email', 'phone', 'company', 'status',
            'source', 'score', 'notes', 'service_interest',
        ]));

        AuditLog::record('api:lead_updated', $lead, $old, $lead->fresh()->toArray());

        return response()->json($lead->fresh());
    }
}
