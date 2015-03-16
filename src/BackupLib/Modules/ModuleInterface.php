<?php

namespace BackupLib\Modules;

interface ModuleInterface
{

    /**
     * @param array $settings
     */
    public function __construct(array $settings);

    /**
     * Get files to backup
     * @return array
     */
    public function getFiles();

}