<?php

namespace Fievel\WebSpider\Components\Logger;

interface LoggerInterface
{
    /**
     * @param $message
     * @param array $context
     */
    public function debug($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function info($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function notice($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function warning($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function error($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function critical($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function alert($message, array $context = []);

    /**
     * @param $message
     * @param array $context
     */
    public function emergency($message, array $context = []);
}