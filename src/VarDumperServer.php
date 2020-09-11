<?php
/**
 * **Var Dumper Server**: https://symfony.com/doc/current/components/var_dumper.html
 * The dump() function outputs its contents in the same browser window or console terminal as your own application. Sometimes mixing the real output with the debug output can be confusing. That’s why this component provides a server to collect all the dumped data.
 * Start the server with the server:dump command and whenever you call to dump(), the dumped data won’t be displayed in the output but sent to that server, which outputs it to its own console or to an HTML file:
 * @installation: composer require --dev symfony/var-dumper
 * @usage: Server launch:
    * `./vendor/bin/var-dump-server`
    * `./vendor/bin/var-dump-server --format=html > dump.html`
 */

namespace Jgauthi\Component\Debug;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\VarDumper;

class VarDumperServer
{
    /**
     * @param string  $host The server host
     */
    public static function init($host = 'tcp://127.0.0.1:9912')
    {
        if (!class_exists('Symfony\Component\VarDumper\Cloner\VarCloner')) {
            die('var-dumper not installed.');
        }

        $cloner = new VarCloner;
        $fallbackDumper = in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) ? new CliDumper : new HtmlDumper;
        $dumper = new ServerDumper($host, $fallbackDumper, [
            'cli' => new CliContextProvider,
            'source' => new SourceContextProvider,
        ]);

        VarDumper::setHandler(function ($var) use ($cloner, $dumper) {
            $dumper->dump($cloner->cloneVar($var));
        });
    }
}
