<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisitMiddleware
{
    public function __construct(protected AnalyticsService $analyticsService) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')
            && !$request->is('admin*')
            && !$request->ajax()
            && !$request->wantsJson()
        ) {
            $this->analyticsService->trackVisit();
        }

        return $next($request);
    }
}
