<?php
namespace Jgauthi\Component\Debug;

use SqlFormatter;

class DebugUtils
{
    /**
     * Allows you to return a well-formatted and clean SQL html from a query
     * @todo Improve by adding an argument $htmlFormat = true|false
     */
    static public function SqlClean(string $query, ?array $arguments): string
    {
        if (!empty($arguments)) {
            $query = str_replace(
                array_map(function ($key) { return ':' . $key; }, array_keys($arguments)),
                array_map(function ($val) { return ((is_int($val)) ? $val : "'{$val}'"); }, array_values($arguments)),
                $query
            );
        }

        return SqlFormatter::format($query);
    }
}