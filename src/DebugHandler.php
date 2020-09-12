<?php
namespace Jgauthi\Component\Debug;

use ReflectionClass;

class DebugHandler
{
    // Affichage d'un rapport de debug en bas de page, pour l'activer:
    // 	* ?debug dans l'url de la page
    // 	* Ou un cookie debug
    // 	* Argument (optionnel):
    // 		* valeur: aucune (debug avec les paramètres par défaut)
    // 		* valeur: global (affiche la variable $GLOBALS)
    // 		* valeur: false (possibilité de désactiver le debug sans supprimer la variable)
    // 		* valeur: defined (ajoute les valeurs define(), peut se cumuler)
    // 		* valeur: function (ajoute les fonctions init dans l'apply, peut se cumuler)
    // 		* valeur: class (ajoute les class init dans l'apply, peut se cumuler)
    // 		* valeur: interface (ajoute les interfaces init dans l'apply, peut se cumuler)
    //
    // Pour cumuler plusieurs valeurs: debug=defined|function
    static public function init(): void
    {
        // Désactiver volontairement le debug
        if (isset($_GET['nodebug'])) {
            return;

        // Ne pas activer le debug dans les requêtes AJAX (wc => WooCommerce)
        } elseif (isset($_GET['wc-ajax'])) {
            return;

        } elseif (isset($_GET['phpinfo'])) {
            phpinfo();
            exit;

            // Afficher le rapport si debug activé, essayer d'éviter de le placer dans les requêtes AJAX
        } elseif (isset($_GET['debug'])) {
            self::activeDebug($_GET['debug']);

            // Wordpress - Refresh cache
            if (is_dir($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/w3-total-cache')) {
                $_GET['w3tc_note'] = 'flush_all';
            }

        } elseif (!empty($_COOKIE['debug']) && !preg_match('#(json|ajax)#i', $_SERVER['REQUEST_URI'])) {
            self::activeDebug($_COOKIE['debug']);
        }
    }

    /**
     * @param string|bool $debugMode
     */
    static private function activeDebug($debugMode): void
    {
        if ($debugMode == 'false') {
            return;
        }

        ini_set('display_errors', true); // 'On'
        ini_set('display_startup_errors', true);

        register_shutdown_function([__CLASS__, 'rapportFooter'], $debugMode);
    }

    /**
     * @param string|bool $debugMode
     * @throws \ReflectionException
     */
    static public function rapportFooter($debugMode = true): void
    {
        // Mettre fin au timer
        $duration = ceil((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);

        $filterDir = [realpath($_SERVER['DOCUMENT_ROOT'])];
        $secondFilter = realpath(dirname(dirname(dirname(dirname(__DIR__)))));
        if ('/' !== $secondFilter && $secondFilter !== $filterDir[0]) {
            $filterDir[] = $secondFilter;
        }

        // Liste des fichiers traités par l'application + Mise en forme
        $listFile = get_included_files();
        $listFile = var_export($listFile, true);
        $listFile = str_replace($filterDir, '', $listFile);

        // Rapport
        $css = 'margin-top: 30px; clear: both; text-align: left;';
        if (defined('WORDPRESS_SCRIPT') && function_exists('is_admin') && is_admin()) {
            $css .= 'margin-left: 170px; margin-right: 10px; padding: 0 10px; border: 1px dashed #'.sprintf('%06d', rand(0, 999999)).';';
        }

        ?><div class="debug" style="<?=$css?>">
        <div style="padding: 10px; width: 50%; float: right;">
            Duree: <?=$duration?> ms, memoire utilise: <?=self::getCurrentMemory()?>
            <?php self::dumpValues($debugMode); ?>
        </div>
        <div style="width: 50%; padding-top: 10px;">
            <pre class="alert alert-info"><?=$listFile?></pre>
        </div>
        </div>
        <div style="clear: both;"></div>
        <?php
    }

    /**
     * Variables à exporter
     * @param string|bool $debugMode
     * @throws \ReflectionException
     */
    static protected function dumpValues($debugMode): void
    {
        $dumpFunction = 'var_dump';
        if (function_exists('dump')) {
            $dumpFunction = 'dump';
        } elseif (function_exists('varExport')) {
            $dumpFunction = 'varExport';
        }

        if ('global' == $debugMode) {
            call_user_func_array($dumpFunction, ['GLOBAL'] + $GLOBALS);
            return;
        }

        $export = [];
        if (!empty($_GET)) {
            $export['_GET'] = $_GET;
        }
        if (!empty($_POST)) {
            $export['_POST'] = $_POST;
        }
        if (!empty($_FILES)) {
            $export['_FILES'] = $_FILES;
        }
        if (!empty($_COOKIE)) {
            $export['_COOKIE'] = $_COOKIE;
        }
        if (!empty($_SESSION)) {
            $export['_SESSION'] = $_SESSION;
        }

        // [Wordpress] Specifics contents
        if (self::isWordpressProject()) {
            global $post;

            if (!empty($post) && is_object($post)) {
                $export['$post'] = $post;
            } elseif (!empty($_REQUEST['post']) && is_numeric($_GET['post']) && function_exists('get_post')) {
                $export['$post'] = get_post($_REQUEST['post']);
            }

            // Post info
            if (!empty($export['$post']->ID)) {
                // Parent
                if (!empty($export['$post']->post_parent)) {
                    $parent = get_post($export['$post']->post_parent);
                    $export['$post_parent'] = ((!empty($parent)) ? $parent : "Le post parent {$export['$post']->post_parent} n'existe pas.");
                }

                // Meta
                if (function_exists('get_post_meta')) {
                    $export['$post_meta'] = get_post_meta($export['$post']->ID);
                }

                // Terms
                if (function_exists('get_post_taxonomies') && function_exists('wp_get_post_terms')) {
                    $terms = [];
                    $taxonomy = get_post_taxonomies($export['$post']->ID);
                    foreach ($taxonomy as $taxo) {
                        $terms = array_merge($terms, wp_get_post_terms($export['$post']->ID, $taxo));
                    }

                    $export['$post_terms'] = $terms;
                }

                // Attachment
                if (function_exists('get_posts') && function_exists('get_post_thumbnail_id')) {
                    $post_attachments = get_posts([
                        'post_type' => 'attachment',
                        'posts_per_page' => -1,
                        'post_parent' => $export['$post']->ID,
                    ]);

                    if (!empty($post_attachments)) {
                        $export['$post_attachments'] = [];
                        foreach ($post_attachments as $attachment) {
                            $export['$post_attachments'][$attachment->ID] = str_replace(get_home_url(), '', $attachment->guid);
                        }
                    }

                    // Image à la une (apparait en 1er dans la liste des Attachments)
                    $post_thumbnail_id = get_post_thumbnail_id($export['$post']->ID);
                    if (isset($export['$post_attachments'][$post_thumbnail_id])) {
                        $attachment = get_post($post_thumbnail_id);
                        asort($export['$post_attachments']);

                        $export['$post_attachments'] = [$attachment->ID => str_replace(get_home_url(), '', $attachment->guid)] + $export['$post_attachments'];
                    }
                }

                // Timber::get_context();
                if (class_exists('Timber')) {
                    $export['Timber::get_context()'] = Timber::get_context();
                }
            }
        }

        if (preg_match('#defined#i', $debugMode)) {
            $export['defined_constants'] = get_defined_constants(true);
            $export['defined_constants'] = $export['defined_constants']['user'];
        }

        if (preg_match('#function#i', $debugMode)) {
            $export['defined_function'] = get_defined_functions();
            $export['defined_function'] = $export['defined_function']['user'];
            unset(
                $export['defined_function'][0],
                $export['defined_function'][1],
                $export['defined_function'][2],
                $export['defined_function'][3],
                $export['defined_function'][4],
                $export['defined_function'][5],
                $export['defined_function'][6]
            );
            $export['defined_function'] = array_values($export['defined_function']);
        }

        if (preg_match('#class#i', $debugMode)) {
            $export['defined_class'] = array_filter(
                get_declared_classes(),
                function ($className) {
                    return !call_user_func([new ReflectionClass($className), 'isInternal']);
                }
            );
            unset($export['defined_class'][array_search('timer', $export['defined_class'], true)]);
            $export['defined_class'] = array_values($export['defined_class']);
        }

        if (preg_match('#interface#i', $debugMode)) {
            $export['defined_interface'] = get_declared_interfaces();
        }

        // Générer export
        $msg = implode(', ', array_keys($export));
        $eval = "'{$msg}'";
        foreach (array_keys($export) as $var_name) {
            $eval .= ', $export[\'' . $var_name . '\']';
        }

        eval($dumpFunction . '(' . $eval . ');'); // varExport($libelle, $var1, $var2, etc);
        unset($export);
    }

    static private function getCurrentMemory(): string
    {
        $memory = memory_get_usage();
        $unit = ['o', 'ko', 'mo', 'go', 'to', 'po'];
        $memory = @round($memory / pow(1024, ($i = floor(log($memory, 1024)))), 2);
        $memory .= ' ' .$unit[$i];

        return $memory;
    }

    static public function isWordpressProject(): bool
    {
        static $isWordpress = null;

        if ($isWordpress === null) {
            $isWordpress = (
                defined('WP_DEBUG') ||
                is_readable($_SERVER['DOCUMENT_ROOT'].'/wp-config.php') ||
                (!empty($_SERVER['REQUEST_URI']) && preg_match('#^/wordpress#i', $_SERVER['REQUEST_URI']))
            );
        }

        return $isWordpress;
    }
}