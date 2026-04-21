<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Smartlink;
use Illuminate\Http\Request;

class SmartlinkController extends Controller
{
    public function redirect(Request $request, string $slug)
    {
        $smartlink = Smartlink::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Try to identify lead by email in query string or cookie
        $lead = null;
        if ($request->has('email')) {
            $lead = Lead::where('email', $request->get('email'))->first();
        }

        $smartlink->processClick(
            $lead,
            $request->ip(),
            $request->userAgent(),
            $request->header('referer')
        );

        return redirect()->away($smartlink->destination_url);
    }
}
