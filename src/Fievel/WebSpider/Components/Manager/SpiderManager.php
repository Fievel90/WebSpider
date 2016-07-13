<?php

namespace Fievel\WebSpider\Components\Manager;

use Fievel\WebSpider\Components\Logger\LoggerTrait;
use Fievel\WebSpider\Components\Middleware\ProxyMiddleware;
use Fievel\WebSpider\Components\Spider\WebSpiderAbstract;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

class SpiderManager
{
    use LoggerTrait;

    const MAX_ITERATION_BEFORE_NEW_PROXY = 3;
    const MAX_ITERATION_BEFORE_SPIDER_ERROR = 15;

    /** @var ProxyManager */
    private $proxyManager;

    /**
     * SpiderManager constructor.
     * @param ProxyManager $proxyManager
     */
    public function __construct(ProxyManager $proxyManager)
    {
        $this->proxyManager = $proxyManager;
    }

    /**
     * @param WebSpiderAbstract $spider
     * @param $method
     * @param array $config
     * @param array $options
     * @param bool $useProxy
     * @param array $proxyTypes
     * @param array $proxyCountries
     *
     * @return mixed|null
     *
     * @throws \InvalidArgumentException
     */
    public function runSpider(
        WebSpiderAbstract $spider,
        $method,
        $config = [],
        $options = [],
        $useProxy = false,
        $proxyTypes = [],
        $proxyCountries = []
    ) {
        $config['handler'] = $this->retryHandler($useProxy, $proxyTypes, $proxyCountries);

        $response = $spider
            ->prepareClient($config)
            ->execute($method, $options);

        if (null !== $response) {
            return $response;
        }

        return null;
    }

    /**
     * @param bool $useProxy
     * @param array $proxyTypes
     * @param array $proxyCountries
     *
     * @return HandlerStack
     */
    protected function retryHandler($useProxy = false, $proxyTypes = [], $proxyCountries = [])
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(ProxyMiddleware::retry(
            function (
                $retries,
                Request $request,
                &$options,
                Response $response = null,
                RequestException $exception = null
            ) use ($useProxy, $proxyTypes, $proxyCountries) {
                if ($retries >= self::MAX_ITERATION_BEFORE_SPIDER_ERROR) {
                    $this->logInfo('Reached max retries');
                    return false;
                }

                if ($useProxy === true && $retries % self::MAX_ITERATION_BEFORE_NEW_PROXY === 0) {
                    $proxy = $this->proxyManager->getRandomProxy($proxyTypes, $proxyCountries);
                    $options[RequestOptions::PROXY] = "tcp://{$proxy->getIp()}:{$proxy->getPort()}";
                }

                if ($exception instanceof ConnectException) {
                    $this->logInfo("Retry #{$retries}");
                    return true;
                }

                if (null !== $response) {
                    $statusCode = $response->getStatusCode();
                    $reasongPhrase = $response->getReasonPhrase();

                    $this->logInfo("HTTP Status Code: {$statusCode} - {$reasongPhrase}");

                    if ($statusCode < 200 || $statusCode >= 300) {
                        $this->logInfo("Retry #{$retries}");
                        return true;
                    }
                }

                return false;
            },
            function ($numberOfRetries) {
                return 1000 * $numberOfRetries;
            }
        ));

        return $handlerStack;
    }
}