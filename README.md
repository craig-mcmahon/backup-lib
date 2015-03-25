# backup-lib

Modular backup library allowing easy creation of both backup and storage modules

## Usage

```php
<?php
$backup = new \BackupLib\Backup(__DIR__ . '/config.yml');

// Optionally set PSR standard logger
$backup->setLogger($logger);

// Start the backup
$backup->run();


```

## Config

Example configuration file

```yaml
settings:
  notification_emails:
    email@dress1.com: Name
    email@dress2.com
jobs:
  Job Name 1:
    module: MySQL
    config:
      method: mysqldump
      user: root
      pass:
      port: 3306
    storage:
      LocalFile:
        location: /tmp/backup
  Job Name 2:
    module: File
    config:
      dirs: /var/www
      exclude: cache
    storage:
      SSH2:
        server: 127.0.0.1
        user: backup
        password:
        key:
        location: /tmp/backup
```


## Modules

### File
#### Config Options
 - dirs
 - exclude

### MySQL
#### Config Options
 - user: root
 - pass:
 - port: 3306
 - host: 127.0.0.1
 - file_per_table: true
 
## Storage

### LocalFile
#### Config Options
 - location = /tmp

### SSH2
Backup using the SSH2 extension to a remote SFTP server

Requires `ext-ssh2`

#### Config Options
- hostname
- user
- pass
- pubKey
- privKey
- remoteLocation
- authType

### Google Drive
Requires `p13eater/google-helper`

#### Config Options
- clientId
- clientSecret
- accessTokenLocation
- refreshTokenLocation