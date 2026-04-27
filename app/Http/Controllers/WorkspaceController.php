<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    /**
     * Set the active country filter for the admin workspace.
     * Empty/missing value clears the filter (= "Global").
     */
    public function setCountry(Request $request)
    {
        $countryId = $request->input('country');

        if (! $countryId) {
            session()->forget('country_filter');
        } elseif (Country::whereKey($countryId)->exists()) {
            session(['country_filter' => (int) $countryId]);
        }

        // Bounce back to the previous page; default to dashboard.
        return back()->withFragment('top') ?: redirect('/admin/dashboard');
    }
}
