# Component Debug
Debug tools like: varExport* functions, timer, server dump, debug handler, SQL Beautifier, etc.

* [VarExport*](src/VarExport.php) functions is a `var_export()` extended, with `<pre></pre>` and multiples arguments $var exported. You can use several streams for output debug var (html comment, debug file, values returned).
* [VarExport*_wp](src/VarExportWordpress.php) functions is a wordpress version of precedents functions. These debug functions is display on site or admin footer. 
* [Class Timer](src/Timer.php) to evaluate time script and specific portion code.
* [Debug Handler](src/DebugHandler.php) is a script who display in the footer some debug information on the current page (dump values _GET, _POST..., files used, time script and memory, phpinfo, etc).


## Prerequisite

* PHP 4 (v1.0), PHP 5.4+ (v1.1), PHP 5.6 (v1.2+) or PHP 7.4 (v2)

## Install
Edit your [composer.json](https://getcomposer.org) (launch `composer update` after edit):
```json
{
  "repositories": [
    { "type": "git", "url": "git@github.com:jgauthi/component_debug.git" }
  ],
  "require-dev": {
    "jgauthi/component_debug": "1.*"
  }
}
```

Define the constant for dump exported variable on the folder (require write permissions):
```php
define('DEBUG_EXPORT_PATH', 'tmp/');
```

For use VarExport*_wp functions (wordpress), you can include the [VarExportWordpress.php](src/VarExportWordpress.php) file on `wp-config.php` or `theme init`:
```php
require_once __DIR__.'/vendor/autoload.php';

if (defined('WP_DEBUG') && WP_DEBUG) {
    include_once __DIR__.'/vendor/jgauthi/component_debug/src/VarExportWordpress.php';
}
```

For use the VarDumperServer (optional): `composer require --dev symfony/var-dumper`, and launch the command on your terminal: `./vendor/bin/var-dump-server`.

## Documentation
You can look at [folder example](example).

