<?php
use Jgauthi\Component\Debug\VarDumperServer;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

VarDumperServer::init('tcp://127.0.0.1:9912');
// Listen dumped values on console with command: ./vendor/bin/var-dump-server

$variable = 'lorem ipsu';
dump(
    $variable,
    time(),
    file_get_contents(__DIR__.'/../readme.md')
);