<?php
use Jgauthi\Component\Debug\DebugHandler;

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

DebugHandler::init();

?>
<h2>Debug Handler</h2>
<p>
    Add <a href="?debug">?debug</a> to current url to display debug handler
    <em>($_GET['debug'] or $_COOKIE['debug'] argument)</em>.
</p>
<p>You can add <a href="?debug=function|class|interface">theses values</a>, separated by <strong>|</strong>:</p>
<ul>
    <li><a href="?debug="><em>no value</em></a>: standard output: $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION</li>
    <li><a href="?debug=global">global:</a> export $GLOBALS</li>
    <li><a href="?debug=false">false:</a> debug off</li>
    <li><a href="?debug=defined">defined:</a> export defined constants</li>
    <li><a href="?debug=function">function:</a> export function defined in current script</li>
    <li><a href="?debug=class">class:</a> export class defined in current script</li>
    <li><a href="?debug=interface">interface:</a> export interface defined in current script</li>
</ul>
<p>Or you can use the ?phpinfo for get server php info.</p>

<h2>Some scripts...</h2>
<?php
$content = file_get_contents(__DIR__.'/../readme.md');
echo nl2br($content);

// Add some values to current script
$_POST = ['firstname' => 'john', 'lastname' => 'doe'];


// [Footer] Debug Handler is displayed at end script