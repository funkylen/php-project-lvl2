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

    foreach ($diff as $key => $item) {
        $offsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * $depth - PREFIX_LENGTH;
        echo str_repeat(' ', $offsetLength);

        if (isDiff($item)) {
            printDiff($item, $depth);
        } elseif (is_array($item)) {
            printComplexValue($key, $item, $depth);
        } else {
            printValue($key, $item);
        }
    }

    if ($depth > 1) {
        $endParenthesisOffsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * ($depth - 1);
        echo str_repeat(' ', $endParenthesisOffsetLength);
    }

    echo '}';
    echo PHP_EOL;
}

function printDiff(array $diff, int $depth): void
{
    echo getPrefix($diff);
    echo getKey($diff);
    echo ': ';

    $value = getValue($diff);

    if (is_array($value)) {
        iter($value, $depth + 1);
    } else {
        echo parseValue($value);
        echo PHP_EOL;
    }
}

function printComplexValue($key, array $value, int $depth): void
{
    echo PREFIX_UNTOUCHED;
    echo $key;
    echo ': ';
    iter($value, $depth + 1);
}

function printValue($key, $value): void
{
    echo PREFIX_UNTOUCHED;
    echo $key;
    echo ': ';

    echo parseValue($value);
    echo PHP_EOL;
}

function parseValue($value): string
{
    return is_string($value) ? $value : json_encode($value);
}

function getPrefix(array $diff): string
{
    switch (\Differ\DiffBuilder\getType($diff)) {
        case TYPE_ADDED:
            return PREFIX_ADDED;
        case TYPE_REMOVED:
            return PREFIX_REMOVED;
        case TYPE_UNTOUCHED:
        default:
            return PREFIX_UNTOUCHED;
    }
}

function isDiff($item): bool
{
    if (!is_array($item)) {
        return false;
    }

    $diffKeys = ['type', 'key', 'value'];

    foreach ($diffKeys as $key) {
        if (!array_key_exists($key, $item)) {
            return false;
        }
    }

    return true;
}
