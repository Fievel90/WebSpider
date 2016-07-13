<?php

namespace Fievel\WebSpider\Bundle\Entity;

use Fievel\WebSpider\Components\Entity\ProxyInterface;

class Proxy implements ProxyInterface
{
    const HTTP_PROXY_TYPE = 'HTTP';
    const HTTPS_PROXY_TYPE = 'HTTPS';
    const POST_PROXY_TYPE = 'POST';
    const SOCKS_PROXY_TYPE = 'SOCKS';

    /** @var integer */
    protected $id;

    /** @var string */
    protected $ip;

    /** @var integer */
    protected $port;

    /** @var string */
    protected $type;

    /** @var string */
    protected $country;

    /** @var \DateTime */
    protected $lastUpdate;

    /** @var integer */
    protected $countErrors;

    /** @var integer */
    protected $status;

    /** @var string */
    protected $origin;

    /**
     * Proxy constructor.
     */
    public function __construct()
    {
        $this->countErrors = 0;
        $this->status = 1;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Proxy
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return Proxy
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return Proxy
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Proxy
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Proxy
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * @param \DateTime $lastUpdate
     * @return Proxy
     */
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountErrors()
    {
        return $this->countErrors;
    }

    /**
     * @param int $countErrors
     * @return Proxy
     */
    public function setCountErrors($countErrors)
    {
        $this->countErrors = $countErrors;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Proxy
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return Proxy
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    public function setLastUpdateValue()
    {
        $this->lastUpdate = new \DateTime();
    }
}