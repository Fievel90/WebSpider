<?php

namespace Fievel\WebSpider\Components\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Fievel\WebSpider\Components\Entity\ProxyInterface;
use Fievel\WebSpider\Components\Entity\Repository\ProxyRepositoryInterface;
use Fievel\WebSpider\Components\Logger\LoggerTrait;
use Fievel\WebSpider\Components\Spider\ProxySpiderInterface;
use Fievel\WebSpider\Components\Spider\ProxyTester\ProxyTesterSpider;

class ProxyManager
{
    use LoggerTrait;

    const MAX_ERROR_BEFORE_DELETE = 12;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * ProxyManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getAllProxy()
    {
        $this->logInfo('Retrieving Proxy list');

        /** @var ProxyRepositoryInterface $repo */
        $repo = $this->em->getRepository('FievelWebSpiderBundle:Proxy');
        return $repo->getAllRandom();
    }

    /**
     * @param array $types
     * @param array $countries
     *
     * @return ProxyInterface|null
     */
    public function getRandomProxy($types = [], $countries = []) {
        $this->logInfo('Retrieving random Proxy');

        /** @var ProxyRepositoryInterface $repo */
        $repo = $this->em->getRepository('FievelWebSpiderBundle:Proxy');

        /** @var ProxyInterface $proxy */
        $proxy = $repo->getOneRandom($types, $countries);
        if (null !== $proxy && $this->testProxy($proxy)) {
            $this->logInfo("Proxy: {$proxy->getIp()}:{$proxy->getPort()}");

            return $proxy;
        }
        $this->logInfo("Couldn't find any Proxy");

        return null;
    }

    /**
     * @param $classList
     */
    public function updateProxyList($classList)
    {
        $this->logInfo('Updating Proxy list');

        /** @var ProxyRepositoryInterface $repo */
        $repo = $this->em->getRepository('FievelWebSpiderBundle:Proxy');

        $proxyList = [];

        if (count($classList) > 0) {
            foreach ($classList as $class) {
                $proxySpider = new $class();

                if ($proxySpider instanceof ProxySpiderInterface
                    && $proxySpider->isActive()
                ) {
                    $proxyList[$proxySpider->getSourceSite()] = $proxySpider->execute();
                }
            }
        }

        foreach ($proxyList as $key => $item) {
            $numItems = count($item);

            $this->logInfo("Inserting {$numItems} Proxy for {$key}");

            foreach ($item as $proxy) {
                if (null !== $proxy) {
                    $repo->insert(
                        $proxy['ip'],
                        $proxy['port'],
                        $proxy['type'],
                        $proxy['country'],
                        $key
                    );
                }
            }
        }

        $this->logInfo('Deleting inactive Proxy');
        $repo->deleteInactive();
    }

    /**
     * @param ProxyInterface $proxy
     * @param null $host
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function testProxy(ProxyInterface $proxy, $host = null)
    {
        /** @var ProxyRepositoryInterface $repo */
        $repo = $this->em->getRepository('FievelWebSpiderBundle:Proxy');

        $spider = new ProxyTesterSpider($host);

        $response = null;
        $numErrors = $proxy->getCountErrors();

        for ($i = 0; $i < 5; $i++) {
            $response = $spider->execute('get');
            if (null === $response) {
                $numErrors++;
            }
            sleep(mt_rand(1, 2));
        }

        $status = $numErrors <= self::MAX_ERROR_BEFORE_DELETE ? 1 : 0;
        $repo->updateErrors($proxy, $numErrors, $status);

        return (bool) $status;
    }
}