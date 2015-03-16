<?php

namespace BackupLib\Storage;

use BackupLib\Exception\BackupException,
   BackupLib\File;

class SSH2 extends BaseStorage
{

    const AUTH_PASSWORD = 1;
    const AUTH_KEY = 2;

    protected $session = null;
    protected $hostname;
    protected $authType = self::AUTH_PASSWORD;
    protected $user = 'backup';
    protected $password = null;
    protected $pubKey = '';
    protected $privKey = '';
    protected $remoteLocation = '/backup';

    public function __construct($settings = array())
    {
        if (!extension_loaded('ssh2')) {
            throw new BackupException(
               'Extension ssh2 not loaded - Try `sudo pecl install ssh2 channel://pecl.php.net/ssh2-0.12`'
            );
        }
        if (isset($settings['hostname'])) {
            $this->hostname = $settings['hostname'];
        } else {
            throw new BackupException('hostname not specified');
        }
        if (isset($settings['user'])) {
            $this->user = $settings['user'];
        }
        if (isset($settings['password'])) {
            $this->password = $settings['password'];
        }
        if (isset($settings['pubKey'])) {
            $this->pubKey = $settings['pubKey'];
        }
        if (isset($settings['privKey'])) {
            $this->privKey = $settings['privKey'];
        }
        if (isset($settings['remoteLocation'])) {
            $this->remoteLocation = $settings['remoteLocation'];
        }
        if (isset($settings['authType'])) {
            switch ($settings['authType']) {
                case 'password':
                    $this->authType = self::AUTH_PASSWORD;
                    break;
                case 'key':
                    $this->authType = self::AUTH_KEY;
                    break;
                default:
                    throw new BackupException('Unknown authType "'.$settings['authType'].'"');
                    break;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function store(File $file)
    {
        if ($this->session === null) {
            $this->connect();
        }

        return ssh2_scp_send(
           $this->session,
           $file->getLocation(),
           $this->remoteLocation.DIRECTORY_SEPARATOR.$file->getName()
        );
    }

    /**
     * Connect to server
     */
    protected function connect()
    {
        $this->session = ssh2_connect($this->hostname);

        switch ($this->authType) {
            case self::AUTH_PASSWORD:
                ssh2_auth_password($this->session, $this->user, $this->password);
                break;
            case self::AUTH_KEY:
                ssh2_auth_pubkey_file($this->session, $this->user, $this->pubKey, $this->privKey, $this->password);
                break;
        }
    }
}