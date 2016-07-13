<?php

namespace Fievel\WebSpider\Bundle\Spiders\Proxy;

use Fievel\WebSpider\Components\Spider\ProxySpiderInterface;
use Fievel\WebSpider\Components\Spider\WebSpiderAbstract;
use Symfony\Component\DomCrawler\Crawler;

class HideMyAssSpider extends WebSpiderAbstract implements ProxySpiderInterface
{
    public function __construct()
    {
        parent::__construct('http://proxylist.hidemyass.com');
    }

    public function getSourceSite()
    {
        return 'hidemyass';
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
            ->filter('#listable > tbody')->eq(0)
            ->filter('tr');

        $values = $trNodes->each(function (Crawler $node, $i) {
            $tdNodes = $node->filter('td');

            if ($tdNodes->count() > 7) {
                $style = $tdNodes->eq(1)->filter('style')->text();
                $ip = trim($tdNodes->eq(1)->html());

                preg_match_all('/\.([^\{]+)\{display:none\}/', $style, $matches, PREG_PATTERN_ORDER);
                $matches = $matches[1];
                foreach ($matches as $class) {
                    $ip = str_replace($class, 'none', $ip);
                }
                $ip = preg_replace(
                    '/<style>.*<\/style>|<span[^>]*none[^>]*>[^>]*<\/span>|<div[^>]*none[^>]*>[^>]*<\/div>|\s/smi',
                    '',
                    $ip
                );
                $ip = trim(strip_tags($ip));
                $ip = filter_var($ip, FILTER_VALIDATE_IP);

                $port = trim($tdNodes->eq(2)->text());
                $port = filter_var($port, FILTER_VALIDATE_INT);

                $country = $tdNodes->eq(3)->text();
                $country = trim($country);

                $type = $tdNodes->eq(6)->text();
                $type = substr(trim($type), 0, 5);

                if (false !== $ip && false !== $port) {
                    return [
                        'ip' => $ip,
                        'port' => $port,
                        'country' => $country,
                        'type' => strtoupper($type)
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