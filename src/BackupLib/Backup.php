<?php

namespace BackupLib;

use BackupLib\Exception\BackupException,
   Psr\Log\LoggerAwareInterface,
   Psr\Log\LoggerInterface,
   Psr\Log\NullLogger,
   Symfony\Component\Yaml\Yaml;

class Backup implements LoggerAwareInterface
{

    /**
     * Config array
     * @var array
     */
    protected $config = array();

    /**
     * Logger
     * @var LoggerInterface
     */
    protected $logger = null;

    public function __construct($configFile)
    {
        if (!file_exists($configFile)) {
            throw new BackupException("Could not find config file '{$configFile}'");
        }
        $this->config = Yaml::parse(file_get_contents($configFile));
    }

    /**
     * Run all backup jobs in config
     */
    public function run()
    {

        // Loop config projects
        foreach ($this->config['jobs'] as $job => $settings) {
            $this->getLogger()
               ->info('Backing up '.$job);
            $success = $this->runJob($settings);
            $this->getLogger()
               ->info('Backup of '.$job.' '.($success ? 'Complete' : 'Failed'));
        }

        //TODO: Reporting
    }

    /**
     * Run a single backup job
     * @param array $jobSettings
     * @return bool
     */
    public function runJob($jobSettings)
    {
        $module = $this->getModule($jobSettings['module'], $jobSettings['config']);
        if ($module === false) {
            $this->getLogger()
               ->warning('Module '.$jobSettings['module'].' not found');

            return false;
        }

        $this->getLogger()
           ->info("Running module ".get_class($module));
        // Get Files
        $files = $module->getFiles();

        // Put files to required storage engine
        foreach ($jobSettings['storage'] as $storageEngineName => $storageConfig) {
            $storageEngine = $this->getStorageEngine($storageEngineName, $storageConfig);
            if ($storageEngine === false) {
                $this->getLogger()
                   ->warning('Storage engine '.$storageEngineName.' not found');
                continue;
            }
            $this->getLogger()
               ->info('Saving to Storage engine: '.get_class($storageEngine));
            foreach ($files as $file) {
                $storageEngine->store($file);
            }
        }

        return true;
    }

    /**
     * Get a module object
     *
     * @param string $moduleName
     * @param object $moduleConfig
     * @return \BackupLib\Modules\BaseModule|false
     */
    protected function getModule($moduleName, $moduleConfig)
    {
        if (!class_exists($moduleName)) {
            $moduleName = '\\BackupLib\\Modules\\'.$moduleName;
            if (!class_exists($moduleName)) {
                return false;
            }
        }

        /** @var \BackupLib\Modules\BaseModule $module */
        $module = new $moduleName($moduleConfig);
        $module->setLogger($this->getLogger());

        return $module;
    }

    /**
     * Get a storage engine object
     *
     * @param string $storageEngineClassName
     * @param object $engineConfig
     * @return \BackupLib\Storage\BaseStorage|false
     */
    protected function getStorageEngine($storageEngineClassName, $engineConfig)
    {
        if (!class_exists($storageEngineClassName)) {
            $storageEngineClassName = '\\BackupLib\\Storage\\'.$storageEngineClassName;
            if (!class_exists($storageEngineClassName)) {
                return false;
            }
        }
        /** @var \BackupLib\Storage\BaseStorage $storageEngine */
        $storageEngine = new $storageEngineClassName($engineConfig);
        $storageEngine->setLogger($this->getLogger());

        return $storageEngine;
    }


    /**
     * Get the logger
     * @return LoggerInterface
     */
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
}
