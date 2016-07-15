<?php

namespace Fievel\WebSpider\Components\Spider;

use Fievel\WebSpider\Components\Entity\SpiderStorage;
use Fievel\WebSpider\Components\Logger\LoggerTrait;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class WebSpiderAbstract
{
    use LoggerTrait;

    const HTTP_METHOD_GET = 'get';
    const HTTP_METHOD_DELETE = 'delete';
    const HTTP_METHOD_HEAD = 'head';
    const HTTP_METHOD_PATCH = 'patch';
    const HTTP_METHOD_POST = 'post';
    const HTTP_METHOD_PUT = 'put';

    /** @var string */
    protected $url;

    /** @var Client */
    static protected $client;

    /** @var Crawler */
    protected $crawler;

    /** @var SpiderStorage */
    protected $storage;

    /** @var array */
    protected $availableMethods = [
        self::HTTP_METHOD_GET,
        self::HTTP_METHOD_DELETE,
        self::HTTP_METHOD_HEAD,
        self::HTTP_METHOD_PATCH,
        self::HTTP_METHOD_POST,
        self::HTTP_METHOD_PUT
    ];

    /**
     * WebSpiderAbstract constructor.
     * @param $url
     * @param array $config
     * @param SpiderStorage|null $storage
     */
    public function __construct($url, $config = [], SpiderStorage $storage = null)
    {
        $this->url = $url;
        $this->crawler = new Crawler();
        $this->storage = $storage;

        if (null === static::$client) {
            static::$client = new Client($config);
        }
    }

    /**
     * @param $method
     * @param array $options
     * 
     * @return mixed|null
     * 
     * @throws \InvalidArgumentException
     */
    public function execute($method, $options = [])
    {
        $method = strtolower($method);
        if (!in_array($method, $this->availableMethods, false)) {
            throw new \InvalidArgumentException("Method $method not supported.");
        }

        $this->crawler->clear();

        try {
            $this->logInfo("Launching spider: {$this->url}");

            /** @var ResponseInterface $response */
            $response = static::$client->$method($this->url, $options);

            if ($this->isSuccessfull($response)) {
                $this->logInfo('Parsing response');

                $data = $this->getDataFromResponse($response);
                return $this->parseData($data);
            }
        } catch (\Exception $e) {
            $this->logCritical("Error: {$e->getMessage()}");
            return $this->handleException($e);
        }

        return null;
    }

    /**
     * @param ResponseInterface $response
     * 
     * @return string
     */
    protected function getDataFromResponse(ResponseInterface $response)
    {
        return (string) $response->getBody();
    }

    /**
     * @param $data
     * 
     * @return mixed
     */
    abstract protected function parseData($data);

    /**
     * @param \Exception $e
     * 
     * @return mixed
     */
    abstract protected function handleException(\Exception $e);

    /**
     * @param ResponseInterface $response
     * 
     * @return bool
     */
    private function isSuccessfull(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
}