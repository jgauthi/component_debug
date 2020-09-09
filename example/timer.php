<?php
use Jgauthi\Component\Debug\Timer;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

$time = new Timer;

$time->chapitre_debut('readfile Readme');
readfile(__DIR__.'/../readme.md');
//somecode...
$time->chapitre_fin('readfile Readme');

$time->chapitre_debut('Sleep 2s');
sleep(2);
$time->chapitre_fin('Sleep 2s');

$time->stop();
echo $time->outPut('html');
