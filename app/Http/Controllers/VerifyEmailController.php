<?php

namespace App\Http\Controllers;

use App\Services\Email\DoubleOptInService;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(string $token, DoubleOptInService $service)
    {
        $lead = $service->verify($token);

        if (!$lead) {
            return response()->view('emails.verify-failed', [], 404);
        }

        return response()->view('emails.verify-success', ['lead' => $lead]);
    }

    public function unsubscribe(Request $request, DoubleOptInService $service)
    {
        $email = $request->get('email');
        if ($email) {
            $service->unsubscribe($email);
        }

        return response()->view('emails.unsubscribed');
    }
}
