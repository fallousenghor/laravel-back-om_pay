<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Authentification;
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
        $auth = Authentification::where('jeton_acces', $token)->first();

        if (! $auth) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        // Check expiration: ensure we pass a proper DateTime/Carbon to the comparison
        if (isset($auth->date_expiration) && $auth->date_expiration) {
            try {
                $expiry = $auth->date_expiration instanceof \DateTimeInterface
                    ? \Carbon\Carbon::instance($auth->date_expiration)
                    : \Carbon\Carbon::parse($auth->date_expiration);

                if (\Carbon\Carbon::now()->gt($expiry)) {
                    return response()->json(['message' => 'Token expired.'], 401);
                }
            } catch (\Throwable $e) {
                // If parsing fails, reject the token
                return response()->json(['message' => 'Invalid token expiration.'], 401);
            }
        }

        $user = Utilisateur::find($auth->id_utilisateur);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 401);
        }

        // Do not call auth()->setUser() because the app uses a custom Mongo user model
        // instead set the request user resolver so $request->user() returns the Utilisateur
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Also make the authentification record available on the request if controllers need it
        $request->attributes->set('authentification', $auth);

        return $next($request);
    }
}
