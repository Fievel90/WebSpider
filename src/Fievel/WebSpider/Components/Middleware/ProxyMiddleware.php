<?php

namespace Fievel\WebSpider\Components\Middleware;

final class ProxyMiddleware
{
    /**
     * Middleware that retries requests based on the boolean result of
     * invoking the provided "decider" function.
     *
     * If no delay function is provided, a simple implementation of exponential
     * backoff will be utilized.
     *
     * @param callable $decider Function that accepts the number of retries,
     *                          a request, request options, [response],
     *                          and [exception] and returns true if the request
     *                          is to be retried.
     * @param callable $delay   Function that accepts the number of retries and
     *                          returns the number of milliseconds to delay.
     *
     * @return callable Returns a function that accepts the next handler.
     */
    public static function retry(callable $decider, callable $delay = null)
    {
        return function (callable $handler) use ($decider, $delay) {
            return new RetryProxyMiddleware($decider, $handler, $delay);
        };
    }
}