<?php

namespace Differ\DiffStringGenerator;

use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getValue;

use const Differ\DiffBuilder\TYPE_ADDED;
use const Differ\DiffBuilder\TYPE_REMOVED;
use const Differ\DiffBuilder\TYPE_UNTOUCHED;

const PREFIX_ADDED = '+ ';
const PREFIX_REMOVED = '- ';
const PREFIX_UNTOUCHED = '  ';
const INDENT_LENGTH = 2;
const PREFIX_LENGTH = 2;

function generateDiffString(array $diff): string
{
    ob_start();
    iter($diff);
    return ob_get_clean();
}

function iter(array $diff, int $depth = 1)
{
    echo '{';
    echo PHP_EOL;

    foreach ($diff as $item) {
        $offsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * $depth - PREFIX_LENGTH;
        echo str_repeat(' ', $offsetLength);
        echo getPrefix($item);
        echo getKey($item);
        echo ': ';

        $value = getValue($item);

        if (is_array($value)) {
            iter($value, $depth + 1);
        } else {
            $parsedValue = is_string($value) ? $value : json_encode($value);
            echo $parsedValue;
            echo PHP_EOL;
        }
    }

    if ($depth > 1) {
        $endParenthesisOffsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * ($depth - 1);
        echo str_repeat(' ', $endParenthesisOffsetLength);
    }

    echo '}';
    echo PHP_EOL;
}

function getPrefix(array $diff): string
{
    switch (\Differ\DiffBuilder\getType($diff)) {
        case TYPE_ADDED:
            return PREFIX_ADDED;
        case TYPE_REMOVED:
            return PREFIX_REMOVED;
        case TYPE_UNTOUCHED:
            return PREFIX_UNTOUCHED;
    }

    throw new \Exception('Undefined Type');
}
