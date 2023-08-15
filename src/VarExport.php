<?php
/**
 * var_export() extended with <pre></pre> and multiples arguments $var exported
 * Formatted value: Null -> # null #, true -> # true #, etc
 */

//-------------------------------------------------
// Debug
//-------------------------------------------------
function varExportReturnValue(mixed $var): string
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

/**
 * Utiliser dump si possible
 */
function varExport(mixed ...$args)
{
    foreach($args as $var) {
        $var = varExportReturnValue($var);
        echo '<pre style="font-size:1.1em;clear:all">'. htmlentities($var, ENT_QUOTES, 'UTF-8') .'</pre><hr />';
    }
    echo '<hr style="color: red;"/>';
}

function varExportData(mixed ...$args): array
{
    $data = [];
    foreach ($args as $var) {
        $data[] = varExportReturnValue($var);
    }

    return $data;
}

function varComment(mixed ...$args): void
{
    echo '<!-- DEBUG:'.PHP_EOL;
    foreach ($args as $var) {
        $var = varExportReturnValue($var);
        echo '=> '.$var.PHP_EOL;
    }
    echo PHP_EOL.'-->'.PHP_EOL;
}

function VarExportFile(mixed ...$args): void
{
    if (!defined('DEBUG_EXPORT_PATH')) {
        die('DEBUG_EXPORT_PATH is not defined');
    } elseif (!is_writable(DEBUG_EXPORT_PATH)) {
        die('The folder DEBUG_EXPORT_PATH is not writable or not exists');
    }

    $trace = (new Exception)->getTrace();
    $originError = 'unknown';
    if (!empty($trace[1])) {
        $originError = $trace[1]['file'].':'.$trace[1]['line']; /* @phpstan-ignore-line */
        if (!empty($trace[1]['function'])) {
            $originError .= ', function: '.$trace[1]['function'];
        }
    }

    $content = 'Origin: '.$originError.PHP_EOL;
    foreach ($args as $var) {
        $var = varExportReturnValue($var);
        $content .= $var.PHP_EOL.'--'.PHP_EOL;
    }
    $content .= PHP_EOL."------------------------------------".PHP_EOL.PHP_EOL;

    // Gestion du fichier
    $file = DEBUG_EXPORT_PATH.'/'.
        preg_replace("#\.[a-z0-9]{1,5}$#", '', basename($_SERVER['PHP_SELF'])).
        '_'.date('dmy').
        '.debug';

    file_put_contents($file, $content, FILE_APPEND);
    chmod($file, 0664);
}

function varExportError(): void
{
    set_error_handler('varExportErrorFunc');
}

function varExportErrorFunc($errno, $errstr, $errfile, $errline): void
{
    VarExportFile("/!\ Erreur: $errstr in file '$errfile:$errline'");
}

/**
 * @return mixed
 * Exemple:
 * 		varExportFunc('function_name', array($arg1, $arg2...))
 * 		varExportFunc(function() { // somecode... });
 */
function varExportFunc(callable $func, array $args = [])
{
    ob_start();
    $return = call_user_func_array($func, $args);
    $content = ob_get_clean();

    $function_name = ((is_string($func)) ? (string) $func : 'Closure ');
    varExport(trim($function_name).' (function_name, args, return, echo_content)', $args, $return, $content);

    return $return;
}
