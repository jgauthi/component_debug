# Component Debug
Debug tools like: varExport* functions, timer, server dump, debug handler, SQL Beautifier, etc.

* [Class Timer](src/Timer.php) to evaluate time script and specific portion code.

`VarExport*` functions is a `var_export()` extended, with `<pre></pre>` and multiples arguments $var exported. You can use several streams for output debug var (html comment, debug file, values returned).


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


## Documentation
You can look at [folder example](https://github.com/jgauthi/component_debug/tree/master/example).

