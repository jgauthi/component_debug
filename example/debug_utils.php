<?php
use Jgauthi\Component\Debug\DebugUtils;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

$request = 'SELECT * FROM article WHERE id = :id AND title = :title';
$arguments = ['id' => 1, 'title' => 'Lorem Ipsu'];

echo DebugUtils::SqlClean($request, $arguments);
