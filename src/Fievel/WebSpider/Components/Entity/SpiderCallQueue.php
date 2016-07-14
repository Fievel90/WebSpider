<?php

namespace Fievel\WebSpider\Components\Entity;

class SpiderCallQueue
{
    /** @var \SplQueue */
    private $queue;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function dequeue()
    {
        return $this->queue->dequeue();
    }

    public function enqueue(
        $spiderClass,
        $url,
        $method,
        $config = [],
        $options = [],
        $useProxy = false,
        $proxyTypes = [],
        $proxyCountries = []
    ) {
        $values = [
            $spiderClass, $url, $method, $config, $options,
            $useProxy, $proxyTypes, $proxyCountries
        ];
        $this->queue->enqueue($values);
        return $this;
    }

    public function getIterator()
    {
        return $this->queue;
    }
}