<?php

namespace BackupLib\Storage;

use BackupLib\File;

interface StorageInterface
{

    /**
     * Store a file
     * @param File $file
     */
    public function store(File $file);

}