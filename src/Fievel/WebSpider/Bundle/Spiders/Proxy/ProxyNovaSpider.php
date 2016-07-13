<?php

namespace Fievel\WebSpider\Bundle\Spiders\Proxy;

use Fievel\WebSpider\Components\Spider\ProxySpiderInterface;
use Fievel\WebSpider\Components\Spider\WebSpiderAbstract;
use Symfony\Component\DomCrawler\Crawler;

class ProxyNovaSpider extends WebSpiderAbstract implements ProxySpiderInterface
{
    public function __construct()
    {
        parent::__construct('http://www.proxynova.com/proxy-server-list/');
    }

    public function getSourceSite()
    {
        return 'proxynova';
    }

    public function isActive()
    {
        return true;
    }

    public function execute($method = 'get', $options = [])
    {
        return parent::execute($method, $options);
    }

    protected function parseData($data)
    {
        $this->crawler->addHtmlContent($data);

        $trNodes = $this->crawler
            ->filter('#tbl_proxy_list > tbody')->eq(0)
            ->filter('tr');

        $values = $trNodes->each(function (Crawler $node, $i) {
            $tdNodes = $node->filter('td');

            if ($tdNodes->count() > 5) {
                $ip = trim($tdNodes->eq(0)->text());
                $ip = filter_var($ip, FILTER_VALIDATE_IP);

                $port = trim($tdNodes->eq(1)->text());
                $port = filter_var($port, FILTER_VALIDATE_INT);

                $country = explode('-', $tdNodes->eq(5)->text());
                $country = trim($country[0]);

                if (false !== $ip && false !== $port) {
                    return [
                        'ip' => $ip,
                        'port' => $port,
                        'country' => $country,
                        'type' => 'HTTPS'
                    ];
                }
            }
            return null;
        });

        return $values;
    }

    protected function handleException(\Exception $e)
    {
        return null;
    }
}