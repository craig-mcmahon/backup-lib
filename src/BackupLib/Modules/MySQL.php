<?php

namespace BackupLib\Modules;

use BackupLib\File;

class MySQL extends BaseModule
{

    protected $user = 'root';
    protected $pass = '';
    protected $host = '127.0.0.1';
    protected $port = 3306;
    protected $filesPerTable = true;
    protected $tempDir = '/tmp/mysql-backup';
    protected $excludeDatabases = array(
       'information_schema',
       'performance_schema',
       'mysql'
    );

    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        if (isset($settings['user'])) {
            $this->user = $settings['user'];
        }
        if (isset($settings['pass'])) {
            $this->pass = $settings['pass'];
        }
        if (isset($settings['host'])) {
            $this->host = $settings['host'];
        }
        if (isset($settings['port'])) {
            $this->port = $settings['port'];
        }
        if (isset($settings['filePerTable'])) {
            $this->filesPerTable = $settings['filePerTable'];
        }
        if (isset($settings['tempDir'])) {
            $this->filesPerTable = $settings['tempDir'];
        }
        if (isset($settings['excludeDatabases'])) {
            $this->filesPerTable = $settings['excludeDatabases'];
        }

    }

    /**
     * @inheritdoc
     */
    public function getFiles()
    {
        $this->makeOrEmptyDir($this->tempDir);
        $flags      = '--single-transaction --routines --triggers';
        $dateString = date('YmdHis');
        $files      = [];
        $conn       = mysqli_connect($this->host, $this->user, $this->pass, '', $this->port);
        $dbResult   = mysqli_query($conn, 'SHOW DATABASES');
        while ($dbRow = $dbResult->fetch_assoc()) {
            $database = $dbRow['Database'];
            if (in_array($database, $this->excludeDatabases)) {
                $this->getLogger()
                   ->debug("Skipping backup of database {$database}");
                continue;
            }
            $this->getLogger()
               ->info("Backing up database {$database}");
            $this->makeOrEmptyDir($this->tempDir.DIRECTORY_SEPARATOR.$database);
            if ($this->filesPerTable) {
                $tableResult = mysqli_query($conn, "SHOW TABLES FROM {$database}");
                if ($tableResult === false) {
                    $this->getLogger()
                       ->warning("No tables found for database {$database}");
                    continue;
                }
                while ($tableRow = $tableResult->fetch_assoc()) {
                    $table = $tableRow["Tables_in_{$database}"];
                    $this->getLogger()
                       ->info("Backing up table {$database}.{$table}");
                    $cmd = "mysqldump --user={$this->user} --pass={$this->pass} --host={$this->host} {$flags} {$database} {$table} \
                        > /tmp/mysql-backup/{$database}/{$table}.sql";
                    $this->exec($cmd);
                }
            } else {
                $cmd = "mysqldump --user={$this->user} --pass={$this->pass} --host={$this->host} {$flags} {$database} \
                     > /tmp/mysql-backup/{$database}/dump.sql";
                $this->exec($cmd);
            }
            $this->createArchive("/tmp/mysql-backup/{$database}.{$dateString}.tar.gz", "/tmp/mysql-backup/{$database}");
            $files[] = new File("{$database}.tar.gz", "/tmp/mysql-backup/{$database}.tar.gz");
        }

        return $files;
    }
}