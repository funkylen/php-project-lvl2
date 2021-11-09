<?php

namespace Differ\DiffStringGenerator;

use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getPrefix;
use function Differ\DiffBuilder\getValue;

function generateDiffString(array $diff): string
{
    ob_start();
    iter($diff);
    return ob_get_clean();
}

function iter(array $diff, int $depth = 1)
{
    $indentLength = 2;
    $prefixLength = 2;

    echo '{';
    echo PHP_EOL;

    foreach ($diff as $d) {
        $offsetLength = ($indentLength + $prefixLength) * $depth - $prefixLength;
        echo str_repeat(' ', $offsetLength);
        echo getPrefix($d);
        echo getKey($d);
        echo ': ';

        $value = getValue($d);

        if (is_array($value)) {
            iter($value, $depth + 1);
        } else {
            $parsedValue = is_string($value) ? $value : json_encode($value);
            echo $parsedValue;
            echo PHP_EOL;
        }
    }

    if ($depth > 1) {
        $endParenthesisOffsetLength = ($indentLength + $prefixLength) * ($depth - 1);
        echo str_repeat(' ', $endParenthesisOffsetLength);
    }

    echo '}';
    echo PHP_EOL;
}
