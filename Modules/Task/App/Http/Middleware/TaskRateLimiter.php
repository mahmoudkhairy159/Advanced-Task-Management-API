<?php

namespace Modules\Task\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TaskRateLimiter
{
    /**
     * The rate limiter instance.
     */
    protected $limiter;

    /**
     * Create a new rate limiter middleware.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts())) {
            return $this->buildResponse($key);
        }

        $this->limiter->hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $this->maxAttempts(),
            $this->calculateRemainingAttempts($key),
            $this->decayMinutes()
        );
    }

    /**
     * Resolve the request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(implode('|', [
            $request->ip(),
            $request->user()?->id ?? 'guest',
            $request->path(),
        ]));
    }

    /**
     * Get the maximum number of attempts for the rate limiter.
     */
    protected function maxAttempts(): int
    {
        return 10; // Maximum 10 attempts
    }

    /**
     * Get the number of minutes to decay the rate limiter.
     */
    protected function decayMinutes(): int
    {
        return 1; // Per minute
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key): int
    {
        return $this->limiter->retriesLeft($key, $this->maxAttempts());
    }

    /**
     * Build the response for when the rate limit is exceeded.
     */
    protected function buildResponse(string $key): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'message' => 'Too many task creation attempts. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429);
    }

    /**
     * Add the rate limit headers to the response.
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, int $retryAfter): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
            'X-RateLimit-Reset' => $retryAfter * 60,
        ]);

        return $response;
    }
}
