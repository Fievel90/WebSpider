<?php

namespace Fievel\WebSpider\Components\Logger;

trait LoggerTrait
{
    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * @param $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logDebug($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logInfo($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logNotice($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->notice($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logWarning($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->warning($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logError($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->error($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logCritical($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->critical($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logAlert($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->alert($message, $context);
        }
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logEmergency($message, array $context = [])
    {
        if (null !== $this->logger) {
            $this->logger->emergency($message, $context);
        }
    }
}