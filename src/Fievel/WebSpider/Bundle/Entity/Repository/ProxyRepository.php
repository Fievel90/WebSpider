<?php

namespace Fievel\WebSpider\Bundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Fievel\WebSpider\Bundle\Entity\Proxy;
use Fievel\WebSpider\Components\Entity\Repository\ProxyRepositoryInterface;

class ProxyRepository extends EntityRepository implements ProxyRepositoryInterface
{
    public function getAllRandom()
    {
        $sql = 'SELECT * FROM proxy WHERE status = 1 ORDER BY RAND()';

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Fievel\WebSpider\Bundle\Entity\Proxy', 'p');

        $query = $this->_em->createNativeQuery($sql, $rsm);
        return $query->getResult();
    }

    public function getOneRandom($types = [], $countries = [])
    {
        $sql = 'SELECT SQL_NO_CACHE * FROM proxy WHERE status = 1';
        $params = [];

        if (count($types) > 0) {
            $sql .= ' AND type IN (:types)';
            $params['types'] = implode(',', $types);
        }

        if (count($countries) > 0) {
            $sql .= ' AND country IN (:countries)';
            $params['countries'] = implode(',', $countries);
        }

        $sql .= ' ORDER BY RAND() LIMIT 1';

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('Fievel\WebSpider\Bundle\Entity\Proxy', 'p');

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameters($params);
        return $query->getOneOrNullResult();
    }

    public function countAll($ip, $port, $type)
    {
        $sql = 'SELECT COUNT(*) as num FROM proxy WHERE ip = :ip AND port = :port AND type = :type';

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute([
            'ip' => $ip,
            'port' => $port,
            'type' => $type
        ]);
        $result = $query->fetch();

        return (int) $result['num'];
    }

    public function insert($ip, $port, $type, $country, $origin)
    {
        $num = $this->countAll($ip, $port, $type);
        if ($num > 0) {
            return;
        }

        $proxy = new Proxy();
        $proxy
            ->setIp($ip)
            ->setPort($port)
            ->setType($type)
            ->setCountry($country)
            ->setOrigin($origin);

        $this->_em->persist($proxy);
        $this->_em->flush();
    }

    public function deleteInactive()
    {
        $sql = 'DELETE FROM proxy WHERE status = 0 AND last_update < DATE_SUB(NOW(), INTERVAL 30 MINUTE)';

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetch();
    }

    public function softDelete($id)
    {
        $sql = 'UPDATE proxy SET status = 0 WHERE id = :id';

        $query = $this->_em->getConnection()->prepare($sql);
        $query->execute([
            'id' => $id
        ]);
        return $query->fetch();
    }

    public function updateErrors($proxy, $numErrors, $status)
    {
        /** @var Proxy $proxy */
        $proxy
            ->setCountErrors($numErrors)
            ->setStatus($status);

        $this->_em->persist($proxy);
        $this->_em->flush();
    }
}