<?php

namespace Fievel\WebSpider\Components\Spider\ProxyTester;

use Fievel\WebSpider\Components\Spider\WebSpiderAbstract;

class ProxyTesterSpider extends WebSpiderAbstract
{
    public function __construct($host)
    {
        $url = "$host/fievel-web-spider/test?t=" . time();
        parent::__construct($url);
    }

    protected function parseData($data)
    {
        return $data;
    }

    protected function handleException(\Exception $e)
    {
        return null;
    }
}