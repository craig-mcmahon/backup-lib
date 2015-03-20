<?php

namespace BackupLib\Storage;

use BackupLib\Exception\BackupException;
use BackupLib\File;
use GoogleHelper\Drive\FilesHelper;
use GoogleHelper\GoogleHelper;

class GoogleDrive extends BaseStorage
{

    protected $baseFolderName = 'backup';
    /** @var \Google_Service_Drive_DriveFile */
    protected $baseFolder = null;
    /** @var FilesHelper */
    protected $filesHelper = null;

    protected $accessTokenLocation = '/tmp/backup-google-drive-access-token';
    protected $refreshTokenLocation = '/tmp/backup-google-drive-refresh-token';

    public function __construct($settings = array())
    {
        if (!isset($settings['clientId'])) {
            throw new BackupException('clientId not provided');
        }
        if (!isset($settings['clientSecret'])) {
            throw new BackupException('clientSecret not provided');
        }
        if (isset($settings['accessTokenLocation'])) {
            $this->accessTokenLocation = $settings['accessTokenLocation'];
        }
        if (isset($settings['refreshTokenLocation'])) {
            $this->refreshTokenLocation = $settings['refreshTokenLocation'];
        }

        $googleHelper = new GoogleHelper($settings['clientId'], $settings['clientSecret']);
        $this->filesHelper = new FilesHelper($googleHelper);
        if (file_exists($this->accessTokenLocation)) {
            $googleHelper->setAccessToken(file_get_contents($this->accessTokenLocation));
        }
        if (file_exists($this->refreshTokenLocation)) {
            $googleHelper->setRefreshToken(file_get_contents($this->refreshTokenLocation));
        }
        $googleHelper->auth(GoogleHelper::AUTH_TYPE_CMD_LINE);
        // Save access and refresh tokens
        file_put_contents($this->accessTokenLocation, $googleHelper->getAccessToken());
        file_put_contents($this->refreshTokenLocation, $googleHelper->getRefreshToken());
        $this->baseFolder = $this->filesHelper->getFolderByName($this->baseFolderName, false, null, true);
    }

    /**
     * @inheritdoc
     */
    public function store(File $file)
    {
        $this->filesHelper->uploadFile($file->getName(), $file->getLocation(), $this->baseFolder);
    }


}