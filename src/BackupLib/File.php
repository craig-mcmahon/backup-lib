<?php

namespace BackupLib;

class File
{
    /**
     * File Name
     * @var string
     */
    protected $name = '';

    /**
     * File Location
     * @var string
     */
    protected $location = '';

    public function __construct($name, $location)
    {
        $this->name     = $name;
        $this->location = $location;
    }

    /**
     * Get File Name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get File Location
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

}