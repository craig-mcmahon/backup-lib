<?php

namespace BackupLib\Storage;

use BackupLib\File;

class LocalFile extends BaseStorage
{

    protected $location = '/tmp';

    public function __construct($settings = array())
    {
        if (isset($settings['location'])) {
            $this->location = $settings['location'];
        }
        if (!is_dir($this->location)) {
            mkdir($this->location);
        }
    }

    /**
     * @inheritdoc
     */
    public function store(File $file)
    {
        copy($file->getLocation(), $this->location.DIRECTORY_SEPARATOR.$file->getName());
    }

}