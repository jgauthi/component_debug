<?php
use Jgauthi\Component\Debug\Timer;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

// Use timer without set manually outPut method
$timer = Timer::init(false, Timer::EXPORT_FORMAT_COMMENT);
// OR
// $timer = Timer::init();
// OR
// Timer::init(); // if you don't need the var '$timer'


// somecode
// ...
$timer->step('Some code');

// The timer will be displayed at footer