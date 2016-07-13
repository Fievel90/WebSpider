<?php

namespace Fievel\WebSpider\Components\Entity\Repository;

use Fievel\WebSpider\Components\Entity\ProxyInterface;

interface ProxyRepositoryInterface
{
    /**
     * @return array
     */
    public function getAllRandom();

    /**
     * @param array $types
     * @param array $countries
     *
     * @return ProxyInterface|null
     */
    public function getOneRandom($types = [], $countries = []);

    /**
     * @param $ip
     * @param $port
     * @param $type
     *
     * @return integer
     */
    public function countAll($ip, $port, $type);

    /**
     * @param $ip
     * @param $port
     * @param $type
     * @param $country
     * @param $origin
     */
    public function insert($ip, $port, $type, $country, $origin);

    /**
     * @return mixed
     */
    public function deleteInactive();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function softDelete($id);

    /**
     * @param $proxy
     * @param $numErrors
     * @param $status
     */
    public function updateErrors($proxy, $numErrors, $status);
}