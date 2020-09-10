<?php
/**
 * var_export() extended with <pre></pre> and multiples arguments $var exported
 * Formatted value: Null -> # null #, true -> # true #, etc
 */

//-------------------------------------------------
// Debug
//-------------------------------------------------
/**
 * @param mixed $var
 * @return string
 */
function varExportReturnValue($var)
{
    if (null === $var) {
        $var = '# NULL #';
    } elseif (false === $var) {
        $var = '# false #';
    } elseif (true === $var) {
        $var = '# true #';
    } elseif ('' === $var) {
        $var = '# empty #';
    } elseif (is_string($var)) {
        $var = stripslashes(mb_substr(var_export(wordwrap($var, 160), true), 1, -1));
    } elseif (is_numeric($var)) {
        $var = var_export(wordwrap($var, 160), true);
    } else {
        $var = var_export($var, true);
    }

    return $var;
}

// Utiliser dump si possible
function varExport()
{
    $args = func_get_args();
    foreach($args as $var) {
        $var = varExportReturnValue($var);
        echo '<pre style="font-size:1.1em;clear:all">'. htmlentities($var, ENT_QUOTES, 'UTF-8') .'</pre><hr />';
    }
    echo '<hr style="color: red;"/>';
}

/**
 * @param mixed ...$args
 * @return array
 */
function varExportData()
{
    $args = func_get_args();
    $data = [];
    foreach ($args as $var) {
        $data[] = varExportReturnValue($var);
    }

    return $data;
}

function varComment()
{
    $args = func_get_args();

    echo "<!-- DEBUG:\n";
    foreach ($args as $var) {
        $var = varExportReturnValue($var);
        echo "=> $var\n";
    }
    echo "\n-->\n\n";
}

/**
 * @param mixed ...$args
 */
function VarExportFile()
{
    if (!defined('DEBUG_EXPORT_PATH')) {
        die('DEBUG_EXPORT_PATH is not defined');
    } elseif (!is_writable(DEBUG_EXPORT_PATH)) {
        die('The folder DEBUG_EXPORT_PATH is not writable or not exists');
    }

    $args = func_get_args();
    $content = '';
    foreach ($args as $var) {
        $var = varExportReturnValue($var);
        $content .= "$var\n--\n";
    }
    $content .= "\n------------------------------------\n\n";

    // Gestion du fichier
    $file = DEBUG_EXPORT_PATH.'/'.
        preg_replace("#\.[a-z0-9]{1,5}$#", '', basename($_SERVER['PHP_SELF'])).
        '_'.date('dmy').
        '.debug';

    file_put_contents($file, $content, FILE_APPEND);
    chmod($file, 0664);
}

function varExportError()
{
    set_error_handler('varExportErrorFunc');
}

/**
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param string $errline
 */
function varExportErrorFunc($errno, $errstr, $errfile, $errline)
{
    VarExportFile("/!\ Erreur: $errstr in file '$errfile:$errline'");
}

/**
 * @param callable $func
 * @param array $args
 * @return mixed
 * Exemple:
 * 		varExportFunc('function_name', array($arg1, $arg2...))
 * 		varExportFunc(function() { // somecode... });
 */
function varExportFunc($func, $args = [])
{
    ob_start();
    $return = call_user_func_array($func, $args);
    $content = ob_get_clean();

    $function_name = ((is_string($func)) ? (string) $func : 'Closure ');
    varExport(trim($function_name).' (function_name, args, return, echo_content)', $args, $return, $content);

    return $return;
}
