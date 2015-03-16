<?php

namespace BackupLib\Storage;

use Psr\Log\LoggerAwareInterface,
   Psr\Log\LoggerInterface,
   Psr\Log\NullLogger;

abstract class BaseStorage implements StorageInterface, LoggerAwareInterface
{

    private $logger = null;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

}