<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class RateLimitingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $userId = $user ? $user->idUtilisateur : $request->ip();

        $routeName = $request->route() ? $request->route()->getName() : $request->path();

        // Définir les limites selon le type de requête
        $limits = $this->getLimits($routeName);

        foreach ($limits as $key => $limit) {
            $cacheKey = "rate_limit:{$userId}:{$key}";

            $requests = Cache::get($cacheKey, []);

            // Nettoyer les anciennes requêtes
            $requests = array_filter($requests, function ($timestamp) use ($limit) {
                return Carbon::createFromTimestamp($timestamp)->addSeconds($limit['window'])->isFuture();
            });

            // Vérifier la limite
            if (count($requests) >= $limit['max']) {
                $resetTime = Carbon::createFromTimestamp(min($requests))->addSeconds($limit['window']);
                $remaining = $resetTime->diffInSeconds(Carbon::now());

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'RATE_LIMIT_EXCEEDED',
                        'message' => 'Limite de requêtes dépassée'
                    ]
                ], 429)->header('X-RateLimit-Limit', $limit['max'])
                      ->header('X-RateLimit-Remaining', 0)
                      ->header('X-RateLimit-Reset', $resetTime->timestamp)
                      ->header('Retry-After', $remaining);
            }

            // Ajouter la nouvelle requête
            $requests[] = Carbon::now()->timestamp;
            Cache::put($cacheKey, $requests, $limit['window']);
        }

        $response = $next($request);

        // Ajouter les headers de rate limiting à la réponse
        if ($limits) {
            $limit = reset($limits);
            $cacheKey = "rate_limit:{$userId}:" . key($limits);
            $requests = Cache::get($cacheKey, []);
            $remaining = max(0, $limit['max'] - count($requests));
            $resetTime = count($requests) > 0 ? Carbon::createFromTimestamp(min($requests))->addSeconds($limit['window']) : Carbon::now();

            $response->headers->set('X-RateLimit-Limit', $limit['max']);
            $response->headers->set('X-RateLimit-Remaining', $remaining);
            $response->headers->set('X-RateLimit-Reset', $resetTime->timestamp);
        }

        return $response;
    }

    private function getLimits($routeName)
    {
        // Limites selon la documentation
        if (str_contains($routeName, 'transferts') || str_contains($routeName, 'paiements')) {
            return [
                'transactions' => ['max' => 20, 'window' => 60] // 20 requêtes/minute pour les transactions
            ];
        }

        return [
            'consultation' => ['max' => 100, 'window' => 60] // 100 requêtes/minute pour la consultation
        ];
    }
}
