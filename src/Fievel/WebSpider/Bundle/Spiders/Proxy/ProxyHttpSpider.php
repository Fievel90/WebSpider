<?php

namespace Fievel\WebSpider\Bundle\Spiders\Proxy;

use Fievel\WebSpider\Components\Spider\ProxySpiderInterface;
use Fievel\WebSpider\Components\Spider\WebSpiderAbstract;
use Symfony\Component\DomCrawler\Crawler;

class ProxyHttpSpider extends WebSpiderAbstract implements ProxySpiderInterface
{
    private $jsCodeParams = [];

    public function __construct()
    {
        parent::__construct('http://proxyhttp.net');
    }

    public function getSourceSite()
    {
        return 'proxyhttp';
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

        $jsCode = null;
        $jsCodes = $this->crawler->filter('script')->each(function (Crawler $node, $i) {
            if (preg_match('/\/\/<!\[CDATA\[(.*)\/\/]]>/smi', $node->text(), $matches)) {
                return $matches[1];
            }
            return null;
        });

        foreach ($jsCodes as $code) {
            if (null !== $code) {
                $jsCode = $code;
                break;
            }
        }

        $jsCode = preg_replace('/([a-zA-Z]+)/smi', "\$this->jsCodeParams['$1']", $jsCode);
        eval($jsCode);

        $trNodes = $this->crawler
            ->filter('.proxytbl')->eq(0)
            ->filter('tr');

        $values = $trNodes->each(function (Crawler $node, $i) {
            $tdNodes = $node->filter('td');

            if ($tdNodes->count() > 3) {
                $ip = trim($tdNodes->eq(0)->text());
                $ip = filter_var($ip, FILTER_VALIDATE_IP);

                $port = trim($tdNodes->eq(1)->html());
                if (preg_match('/document.write\((.+)\)/', $port, $matches)) {
                    $jsCodePort = preg_replace('/([a-zA-Z]+)/smi', "\$this->jsCodeParams['$1']", $matches[1]);
                    $jsCodePort = '$port = ' . $jsCodePort . ';';
                    eval ($jsCodePort);
                }
                $port = filter_var($port, FILTER_VALIDATE_INT);

                $country = trim($tdNodes->eq(2)->text());

                $https = trim($tdNodes->eq(4)->text());
                $type = $https === '-' ? 'HTTP' : 'HTTPS';

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