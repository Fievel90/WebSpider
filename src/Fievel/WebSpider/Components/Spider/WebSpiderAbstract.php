<?php

namespace Fievel\WebSpider\Components\Spider;

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
    protected $client;

    /** @var Crawler */
    protected $crawler;

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
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->crawler = new Crawler();
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function prepareClient($config = [])
    {
        $this->client = new Client($config);
        return $this;
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
        if (null === $this->client) {
            throw new \InvalidArgumentException('Create client before spider execution.');
        }

        $method = strtolower($method);
        if (!in_array($method, $this->availableMethods, false)) {
            throw new \InvalidArgumentException("Method $method not supported.");
        }

        $this->crawler->clear();

        try {
            $this->logInfo("Launching spider: {$this->url}");

            /** @var ResponseInterface $response */
            $response = $this->client->$method($this->url, $options);

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