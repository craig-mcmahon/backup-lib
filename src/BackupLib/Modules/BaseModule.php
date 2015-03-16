<?php

namespace BackupLib\Modules;

use Psr\Log\LoggerAwareInterface,
   Psr\Log\LoggerInterface,
   Psr\Log\NullLogger;

abstract class BaseModule implements ModuleInterface, LoggerAwareInterface
{

    /** @var LoggerInterface */
    private $logger = null;

    protected function getLogger()
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

    /**
     * Executes a given command and returns TRUE if returnCode is 0, otherwise returns returnCode
     * @param $command
     * @return bool|int
     */
    protected function exec($command)
    {
        exec($command, $output, $returnCode);
        if ($returnCode != 0) {
            $message = "Exec of '{$command} failed. Return Code: {$returnCode}. Output: ".print_r($output, true);
            $this->getLogger()
               ->warning($message);

            return $returnCode;
        }

        return true;
    }

    /**
     * Creates a directory, or empties it if it exists
     * @param $dir
     */
    protected function makeOrEmptyDir($dir)
    {
        if (is_dir($dir)) {
            //TODO: EmptyDir + Sanity Check?
        } else {
            mkdir($dir);
        }
    }

    /**
     * TODO: IMPROVE
     * @param $archive
     * @param $location
     * @param string $flags
     * @param array $excludes
     */
    protected function createArchive($archive, $location, $flags = '-czPf', $excludes = array())
    {
        $cmd = 'tar ';
        foreach ($excludes as $exclude) {
            $cmd .= ' --exclude='.$location.DIRECTORY_SEPARATOR.$exclude;
        }
        $cmd .= " {$flags} {$archive} {$location}";
        $this->exec($cmd);
    }
}