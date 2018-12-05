<?php

require 'backup.php';

$backup = new Backup();

// Mysql yedeği almak için
$mysqlBackup = $backup->mysql([
    'host' => 'localhost',
    'user' => 'xx',
    'pass' => 'xx',
    'dbname' => 'xx',
    'file' => __DIR__ . '/backup.sql'
]);
if ($mysqlBackup){
    echo 'Mysql yedeği alındı.';
}

// Klasör yedeği almak için
$folderBackup = $backup->folder([
    'dir' => 'cms',
    'file' => 'yedek.zip',
    'exclude' => ['.idea', 'upload'] // bunlar hariç yedekle
]);
if ($folderBackup){
    echo 'Klasör yedeği alındı!';
}

// Mysql + Klasör yedeğini birlikte almak için
$backup = new Backup([
    'db' => [
        'host' => 'localhost',
        'user' => 'xx',
        'pass' => 'xx',
        'dbname' => 'xx',
        'file' => __DIR__ . '/backup.sql'
    ],
    'folder' => [
        'dir' => 'cms',
        'file' => 'yedek.zip',
        'exclude' => ['.idea', 'upload'] // bunlar hariç yedekle
    ]
]);
$yedekle = $backup->full();
if ($yedekle){
    echo 'Mysql ve Dosyalar Yedeklendi!';
}
