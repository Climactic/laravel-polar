<?php

namespace Climactic\LaravelPolar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    public function handle(Request $request, Closure $next, string $type = 'default', ?string $productId = null): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'subscribed') && $user->subscribed($type, $productId)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Subscription required.'], 403);
        }

        $redirectUrl = config('polar.middleware_redirect_url');

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }

        abort(403, 'Subscription required.');
    }
}
