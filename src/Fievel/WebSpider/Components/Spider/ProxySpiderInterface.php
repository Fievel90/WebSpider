<?php

namespace Fievel\WebSpider\Components\Spider;

interface ProxySpiderInterface
{
    /**
     * @return string
     */
    public function getSourceSite();

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @param string $method
     * @param array $options
     *
     * @return mixed
     */
    public function execute($method = 'get', $options = []);
}