<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $lastSeen = $user->last_seen_at;

            if (! $lastSeen || $lastSeen->lt(now()->subMinute())) {
                $user->markOnline();
            }
        }

        return $next($request);
    }
}
