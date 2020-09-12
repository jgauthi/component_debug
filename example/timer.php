<?php
use Jgauthi\Component\Debug\Timer;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

$time = new Timer;

// Get the time executed in delimited code
$time->chapterStart('readfile Readme');
readfile(__DIR__.'/../readme.md');
//somecode...
$time->chapterEnd('readfile Readme');

$time->chapterStart('Sleep 2s');
sleep(2);
$time->chapterEnd('Sleep 2s');

// Calculate the execution average of a function in a loop with chapters
// In this example: 10 Loops, 100 executions by loop (total: 1000 executions)
$time->testLoop('filemtime', 10, 100, [__FILE__]);

// Define a location in the code to get the current time spent
$time->step('After testLoop');
sleep(1);
$time->step('After sleep 1s');


$time->stop();
echo $time->outPut(Timer::EXPORT_FORMAT_HTML);


// You can export Chapter logs to CSV file
$time->exportChapter(sys_get_temp_dir().'/timer_chapter_export.csv');
