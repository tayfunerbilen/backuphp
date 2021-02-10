# BackupPHP
I prepared this class in lecture that I published on my Youtube channel.

If you watching lectures;

[MySQL Backup Lecture](https://www.youtube.com/watch?v=nEE7c82XEsg) | 
[Folder Backup Lecture](https://www.youtube.com/watch?v=5t_-cUDYyB8)

First, you need to initialize `Backup` class.
```php
$backup = new Backup();
```

For mysql backup;
```php
try {
    $mysqlBackup = $backup->mysql([
        'host' => 'localhost',
        'user' => '',
        'pass' => '',
        'dbname' => '',
        'file' => __DIR__ . '/backup.sql'
    ]);
    if ($mysqlBackup){
        echo 'success';
    }
} catch (Exception $e){
    die($e->getMessage());
}
```

For folder backup;
```php
try {
    $folderBackup = $backup->folder([
        'dir' => 'cms',
        'file' => 'backup.zip',
        'exclude' => ['.idea', 'upload', 'vendor'] // exclude these files while backup
    ]);
    if ($folderBackup){
        echo 'success';
    }
} catch (Exception $e){
    die($e->getMessage());
}
```

For full backup;
```php
try {
    $backup = new Backup([
        'db' => [
            'host' => 'localhost',
            'user' => '',
            'pass' => '',
            'dbname' => '',
            'file' => __DIR__ . '/backup.sql'
        ],
        'folder' => [
            'dir' => 'cms', // directory name
            'file' => 'full_backup.zip',
            'exclude' => ['.idea', 'upload', 'vendor'] // exclude these files while backup
        ]
    ]);
    $yedekle = $backup->full();
    if ($yedekle){
        echo 'success';
    }
} catch (Exception $e){
    die($e->getMessage());
}
```
