<?php

namespace App\Http\Controllers;

use App\Models\CountryReport;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function countryReport(Request $request, CountryReport $report): \Illuminate\View\View
    {
        // Ensure the user is authenticated (admin only)
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        return view('filament.print.country-report', [
            'record' => $report,
        ]);
    }
}
