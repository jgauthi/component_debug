<?php
define('DEBUG_EXPORT_PATH', sys_get_temp_dir());

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

$varText = 'lorem ipsu';
$varTrue = true;
$varFalse = false;

// Export Data with <pre></pre> and formatted value
// like "# null #" for null, "# true #" for true, etc
VarExport(
    $varText,
    $varTrue,
    $varFalse,
    time(),
    file_get_contents(__DIR__.'/../readme.md')
);

// Return values
$valuesExported = varExportData($varText, $varTrue);

// Hide exported Data on HTML comment
varComment($varFalse, $valuesExported);

// Export values on DEBUG_EXPORT_PATH/$debugFIle
// Usefull for debug Ajax request / Api or any request without printed stream
VarExportFile($varText, $varTrue, null);

// Export php error on debug file (use VarExportFile)
varExportError();
echo $varDontExist;

// Debug and export function, display method information (arguments, return value, etc)
varExportFunc('htmlentities', [$varText, ENT_QUOTES, 'UTF-8']);
