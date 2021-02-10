# BackupPHP
I prepared this class in lecture that I published on my Youtube channel.

If you watching lectures;

[MySQL Backup Lecture](https://www.youtube.com/watch?v=nEE7c82XEsg) | 
[Folder Backup Lecture](https://www.youtube.com/watch?v=5t_-cUDYyB8)

# Usage

First, you need to initialize `Backup` class.
```php
$backup = new Backup();
```

For mysql backup;
```php
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
```

For folder backup;
```php
$folderBackup = $backup->folder([
    'dir' => 'cms',
    'file' => 'backup.zip',
    'exclude' => ['.idea', 'upload', 'vendor'] // exclude these files while backup
]);
if ($folderBackup){
    echo 'success';
}
```

For full backup;
```php
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
```
