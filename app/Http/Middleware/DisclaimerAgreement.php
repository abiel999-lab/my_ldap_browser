<?php

namespace App\Http\Middleware;

use App\Helpers\DisclaimerAgreementHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class DisclaimerAgreement
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request):Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUrl = $request->fullUrl();
        $check = DisclaimerAgreementHelper::check();
        if ($check) {
            return $next($request);
        } else {
            $disAgreement = DisclaimerAgreementHelper::goToDisclaimerAgreeement($currentUrl);

            return Redirect::away($disAgreement);
        }

        return response('Unauthorized!', 401);
    }
}
