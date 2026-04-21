<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignArchiveController extends Controller
{
    public function index(Request $request)
    {
        $campaigns = Campaign::where('type', 'email')
            ->where('status', 'completed')
            ->orderByDesc('end_date')
            ->paginate(12);

        return view('campaigns.archive', compact('campaigns'));
    }

    public function show(Campaign $campaign)
    {
        if ($campaign->status !== 'completed') abort(404);

        $emailCampaign = $campaign->emailCampaigns()->first();

        return view('campaigns.show', compact('campaign', 'emailCampaign'));
    }
}
