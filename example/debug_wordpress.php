<?php
//-- Install this code on wp-config.php OR theme init ---------------

// In this example, the vendor folder is located in "example/"
require_once __DIR__.'/vendor/autoload.php';

if (defined('WP_DEBUG') && WP_DEBUG) {
    include_once __DIR__.'/vendor/jgauthi/component_debug/src/VarExportWordpress.php';
}
//------------------------------------------------------------------

// Export Data with <pre></pre> and formatted value, display on footer (site or admin)
$variable = 'lorem ipsu';
varExport_wp(
    $variable,
    time(),
    file_get_contents(__DIR__.'/../readme.md')
);

// Debug and export function, display method information (arguments, return value, etc)
varExportFunc_wp('htmlentities', [$variable, ENT_QUOTES, 'UTF-8']);

// Export filter list from hook
varExportFilter_wp('the_content');

// Compare postmeta between several post ID
varExport_wp_cmp_postmeta($postId = 1, $postId = 2);

// Wordpress list hook
list_hooks();


