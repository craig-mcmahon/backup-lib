<?php

namespace BackupLib\Modules;

use BackupLib\Exception\BackupException;

class File extends BaseModule
{

    protected $dirs = array();

    protected $exclude = array();

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (isset($settings['dirs'])) {
            if (is_array($settings['dirs'])) {
                $this->dirs = $settings['dirs'];
            } else {
                $this->dirs[] = $settings['dirs'];
            }
        }
        if (isset($settings['exclude'])) {
            if (is_array($settings['exclude'])) {
                $this->exclude = $settings['exclude'];
            } else {
                $this->exclude[] = $settings['exclude'];
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        if (count($this->dirs) == 0) {
            throw new BackupException('No Files to backup');
        }

        $files = array();
        foreach ($this->dirs as $dir) {
            if (!file_exists($dir)) {
                $this->getLogger()
                   ->warning('Directory ', $dir, ' does not exist.');
                continue;
            }
            $fileParts = explode(DIRECTORY_SEPARATOR, $dir);
            $fileName  = end($fileParts).'.'.date('YmdHis').'.tar.gz';
            $localFile = '/tmp/'.$fileName;

            $this->createArchive($localFile, $dir, '-czPf', $this->exclude);
            $files[] = new \BackupLib\File($fileName, $localFile);
        }

        return $files;
    }
}