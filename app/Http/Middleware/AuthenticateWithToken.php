<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SessionOmpay;
use App\Models\Utilisateur;
use Carbon\Carbon;

class AuthenticateWithToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = substr($header, 7);
        $session = SessionOmpay::where('token', $token)->first();

        if (! $session) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        // Check if session is still active (last activity within 24 hours)
        if (Carbon::now()->diffInHours($session->last_activity) > 24) {
            return response()->json(['message' => 'Session expired.'], 401);
        }

        // Update last activity
        $session->update(['last_activity' => Carbon::now()]);

        $user = Utilisateur::find($session->utilisateur_id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 401);
        }

        // Do not call auth()->setUser() because the app uses a custom Mongo user model
        // instead set the request user resolver so $request->user() returns the Utilisateur
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Also make the session record available on the request if controllers need it
        $request->attributes->set('session', $session);

        return $next($request);
    }
}
