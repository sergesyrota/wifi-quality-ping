<?php

require_once __DIR__ . '/include.php';

$dir = new DirectoryIterator($_config['pidFilePath']);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isFile()) {
        continue;
    }
    $pid = (int)file_get_contents($fileinfo->getRealPath());
    if ($pid > 0) {
        `kill {$pid}`;
    }
}