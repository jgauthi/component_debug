<?php
/**
 * Debug Wordpress
 * require VarExport.php
 */

namespace Jgauthi\Component\Debug;

use InvalidArgumentException;

// function varExport_add_action($func, $arg, $action = null)
// {
// 	if(empty($action))
// 		$action = ((is_admin()) ? 'admin_footer' : 'wp_footer');

// 	add_action($action, function() use (&$args)
// 	{
// 		echo '<div style="margin-left: 170px; margin-right: 10px; padding: 0 10px; border: 1px dashed #'. sprintf('%06d', rand(0,999999)) .';">';
// 	        call_user_func_array('varExport', $args);
//     	echo '</div><br clear="all">';
// 	});
// }

function varExport_wp(mixed ...$args): void
{
    if (!function_exists('is_admin') || !function_exists('add_action')) {
        return;
    }

    add_action(((is_admin()) ? 'admin_footer' : 'wp_footer'), function () use (&$args) {
        $color = '#'.sprintf('%06d', rand(0, 999999)); ?>
        <div style="margin-left: 170px; margin-right: 10px; padding: 0 10px; border: 1px dashed <?=$color; ?>;">
            <?php call_user_func_array('varExport', $args); ?>
            ?></div>
        <br clear="all">
        <?php
    });
}

function varExportFunc_wp(callable $func, array $args = []): void
{
    if (!function_exists('is_admin') || !function_exists('add_action')) {
        return;
    }

    add_action(((is_admin()) ? 'admin_footer' : 'wp_footer'), function () use (&$func, &$args) {
        $style = 'padding: 0 10px; border: 1px dashed #'.sprintf('%06d', rand(0, 999999)).';';
        $style .= ((is_admin()) ? 'margin-left: 170px; margin-right: 10px; ' : 'margin: 10px;'); ?>
        <div style="<?=$style; ?>">
            <?php varExportFunc($func, ((is_array($args)) ? $args : [$args])); ?>
        </div><br clear="all">
        <?php
    });
}

function varExportFilter_wp(?string $regexp = null): void
{
    if (!function_exists('is_admin') || !function_exists('add_action')) {
        return;
    }

    add_action(((is_admin()) ? 'admin_footer' : 'wp_footer'), function () use (&$regexp) {
        $style = 'padding: 0 10px; border: 1px dashed #'.sprintf('%06d', rand(0, 999999)).';';
        $style .= ((is_admin()) ? 'margin-left: 170px; margin-right: 10px; ' : 'margin: 10px;');

        global $wp_filter;
        if (empty($wp_filter)) {
            varExport_wp('$wp_filter empty');
            return;
        } elseif (empty($regexp)) {
            varExport_wp("regexp empty");
            return;
        }

        $data = [];
        foreach ($wp_filter as $hook => $filter_list_order) {
            if (!preg_match($regexp, $hook)) {
                continue;
            }

            foreach ($filter_list_order as $order => $filter_list) {
                foreach ($filter_list as $filter_hook => $filter) {
                    if (is_array($filter['function']) && !empty($filter['function'][0]) && is_object($filter['function'][0])) {
                        $filter['function'] = get_class($filter['function'][0]).' (class closure)';
                    }

                    $data[$hook][$filter_hook] = $filter + ['order' => $order];
                }
            }

            uasort($data[$hook], function ($a, $b) {
                if ($a['order'] === $b['order']) {
                    return 0;
                }

                return ($a['order'] < $b['order']) ? -1 : 1;
            });
        } ?>
        <div style="<?=$style?>">
            <?php varExport("Filters '$regexp'", $data); ?>
        </div><br clear="all">
        <?php
    });
}

/**
 * http://www.rarst.net/wordpress/debug-wordpress-hooks/
 */
function dump_hook(string $tag, array $hooks): void
{
    ksort($hooks);

    echo "<pre>\t$tag<br>";

    foreach ($hooks as $priority => $hook) {
        echo $priority;

        foreach ($hook as $parameter) {
            if ('list_hook_details' == $parameter['function']) {
                continue;
            }

            echo "\t";

            if (is_string($parameter['function'])) {
                echo $parameter['function'];
            } elseif (is_string($parameter['function'][0])) {
                echo $parameter['function'][0].' -> '.$parameter['function'][1];
            } elseif (is_object($parameter['function'][0])) {
                echo '(object) '.get_class($parameter['function'][0]).' -> '.$parameter['function'][1];
            } else {
                print_r($parameter);
            }

            echo ' ('.$parameter['accepted_args'].') <br>';
        }
    }
    echo '</pre>';
}

/**
 * usage: list_hooks('wp_footer');
 */
function list_hooks(bool $filter = false): void
{
    global $wp_filter;

    $hooks = $wp_filter;
    ksort($hooks);

    foreach ($hooks as $tag => $hook) {
        if (false === $filter || false !== mb_strpos($tag, $filter)) {
            dump_hook($tag, $hook);
        }
    }
}

function list_hook_details(mixed $input = null): mixed
{
    global $wp_filter;

    $tag = current_filter();
    if (isset($wp_filter[$tag])) {
        dump_hook($tag, $wp_filter[$tag]);
    }

    return $input;
}

function list_live_hooks(bool $hook = false): void
{
    if (false === $hook) {
        $hook = 'all';
    }

    add_action($hook, 'list_hook_details', -1);
}

/**
 * Comparer les postmeta de 2 posts ou plus
 */
function varExport_wp_cmp_postmeta(int ...$posts_ids): void
{
    if (empty($posts_ids)) {
        varExport_wp('Posts ID empties');
        return;
    }

    $posts_meta = [];
    foreach ($posts_ids as $post_id) {
        $meta = get_post_meta($post_id);
        if (empty($meta)) {
            continue;
        }

        $posts_meta["Post {$post_id}"] = $meta;
    }

    $data = array_cmp($posts_meta);
    $html = array_to_html_table_title_cmp($data, 'Compare Wordpress post', 'UTF-8');

    add_action(((is_admin()) ? 'admin_footer' : 'wp_footer'), function () use (&$html) {
        $style = 'padding: 0 10px; border: 1px dashed #'.sprintf('%06d', rand(0, 999999)).';';
        $style .= ((is_admin()) ? 'margin-left: 170px; margin-right: 10px; ' : 'margin: 10px;'); ?>
        <div style="<?=$style; ?>"><?=$html?></div><br clear="all"><?php
    });
}


if (!function_exists('array_cmp')) {
    /**
     * Combine multiple arrays to one.
     * @param array ...$args
     */
    function array_cmp(mixed ...$args): ?array
    {
        if (empty($args)) {
            return null;
        } elseif (1 === count($args) && !empty($args[0])) {
            $args = $args[0];
        }

        $args_keys = array_keys($args);
        sort($args_keys);
        $data = [];

        foreach ($args_keys as $product) {
            foreach ($args[$product] as $col_name => $col_value) {
                if (!isset($data[$col_name])) {
                    $data[$col_name] = array_fill_keys($args_keys, null);
                }

                $data[$col_name][$product] = $col_value;
            }
        }
        ksort($data);

        return $data;
    }
}

if (!function_exists('array_to_html_table_title_cmp')) {
    /**
     * Retourne un array sous forme de tableau html avec 3 colonnes (ligne => titre, colonne => produit).
     *
     * @param array $data [ 'title1' => ['product1' => 'val1', 'product2' => 'val2'), 'title2' => [...]] ]
     * @param string|null $title_table optional
     * @param string $encode UTF-8 or ISO-8859-1
     *
     * @return string HTML Table
     */
    function array_to_html_table_title_cmp(array $data, ?string $title_table = null, string $encode = 'UTF-8'): string
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Argument data is empty or is not an array.');
        }

        // Récupérer les données du 1er élément
        foreach ($data as $id => $array) {
            $first_id = $id;
            if (!is_array($array)) {
                throw new InvalidArgumentException('Array no compatible.');
            }

            break;
        }

        $html = '<table class="table table-striped table-hover table-bordered" border="1">';
        if (!empty($title_table)) {
            $html .= '<caption>'.htmlentities($title_table, ENT_QUOTES, $encode).'</caption>';
        }

        // Titre
        $title_list = array_keys($data[$first_id]);
        $html .= '<thead class="thead-dark"><tr><th scope="row">Colonnes</th>';
        foreach ($title_list as $title) {
            $html .= '<th scope="col">'.htmlentities($title, ENT_QUOTES, $encode).'</th>';
        }

        $html .= '</tr></thead>';

        // Contenu
        $html .= '<tbody>';
        foreach ($data as $col => $array) {
            $html .= '<tr><th align="left" scope="row">'.htmlentities($col, ENT_QUOTES, $encode).'</th>';
            foreach ($title_list as $title) {
                $content = ((isset($array[$title])) ? $array[$title] : null);

                if (null === $content || '' === $content) {
                    $content = '&nbsp;';
                } elseif (is_array($content)) {
                    $content = htmlentities(var_export($content, true), ENT_QUOTES, $encode);
                } else {
                    $content = nl2br(htmlentities(trim($content), ENT_QUOTES, $encode));
                }

                $html .= '<td>'.$content.'</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>
	<tfoot>
		<tr>
			<td colspan="'.(count($data[$first_id]) + 1).'">'.count($data).' elements in this table</td>
		</tr>
	</tfoot>
	</table>';

        return $html;
    }
}
