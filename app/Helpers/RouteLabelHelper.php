<?php

namespace App\Helpers;

class RouteLabelHelper
{

    public static function formatLabel(string $routeName): string
    {
        // If there's no dash, just format the whole route name
        if (!str_contains($routeName, '-')) {
            return ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $routeName));
        }

        // If there is a dash, split and use the second part
        [, $labelPart] = explode('-', $routeName, 2);

        return ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $labelPart));
    }
}
