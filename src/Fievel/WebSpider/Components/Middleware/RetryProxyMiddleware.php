<?php

namespace Fievel\WebSpider\Components\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\RetryMiddleware;
use Psr\Http\Message\RequestInterface;

class RetryProxyMiddleware
{
    /** @var callable  */
    private $nextHandler;

    /** @var callable */
    private $decider;

    /**
     * @param callable $decider     Function that accepts the number of retries,
     *                              a request, request options, [response],
     *                              and [exception] and returns true if the request
     *                              is to be retried.
     * @param callable $nextHandler Next handler to invoke.
     * @param callable $delay       Function that accepts the number of retries
     *                              and returns the number of milliseconds to
     *                              delay.
     */
    public function __construct(
        callable $decider,
        callable $nextHandler,
        callable $delay = null
    ) {
        $this->decider = $decider;
        $this->nextHandler = $nextHandler;
        $this->delay = $delay ?: RetryMiddleware::class . '::exponentialDelay';
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        if (!isset($options['retries'])) {
            $options['retries'] = 0;
        }

        $fn = $this->nextHandler;
        return $fn($request, $options)
            ->then(
                $this->onFulfilled($request, $options),
                $this->onRejected($request, $options)
            );
    }

    private function onFulfilled(RequestInterface $req, array $options)
    {
        return function ($value) use ($req, $options) {
            if (!call_user_func_array(
                $this->decider,
                [
                    $options['retries'],
                    $req,
                    &$options,
                    $value,
                    null
                ]
            )) {
                return $value;
            }
            return $this->doRetry($req, $options);
        };
    }

    private function onRejected(RequestInterface $req, array $options)
    {
        return function ($reason) use ($req, $options) {
            if (!call_user_func_array(
                $this->decider,
                [
                    $options['retries'],
                    $req,
                    &$options,
                    null,
                    $reason
                ]
            )) {
                return new RejectedPromise($reason);
            }
            return $this->doRetry($req, $options);
        };
    }

    private function doRetry(RequestInterface $request, array $options)
    {
        $options['delay'] = call_user_func($this->delay, ++$options['retries']);

        return $this($request, $options);
    }
}