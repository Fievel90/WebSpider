<?php

namespace Fievel\WebSpider\Components\Entity;

interface ProxyInterface
{
    /**
     * @return string
     */
    public function getIp();

    /**
     * @return int
     */
    public function getPort();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return \DateTime
     */
    public function getLastUpdate();

    /**
     * @return int
     */
    public function getCountErrors();

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getOrigin();
}