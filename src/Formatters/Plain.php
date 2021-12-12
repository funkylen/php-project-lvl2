<?php

namespace Differ\Formatters\Plain;

use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\getType;
use const Differ\DiffBuilder\TYPE_ADDED;
use const Differ\DiffBuilder\TYPE_REMOVED;

function get(array $diff)
{
    ob_start();
    iter($diff);
    return ob_get_clean();
}

function iter(array $diff, string $path = null)
{
    foreach ($diff as $item) {
        if (isDiff($item)) {
            printDiff($item, $path);
        }
    }
}

function printDiff(array $diff, string $path = null): void
{
    $key = getKey($diff);
    $path = $path === null ? $key : "$path.$key";

    $value = getValue($diff);

    if (is_array($value)) {
        iter($value, $path);
    } else {
        $type = getType($diff);

        if (TYPE_ADDED === $type) {
            echo "Property '$path' was added with value: " . parseValue($value);
            echo PHP_EOL;
        } elseif (TYPE_REMOVED === $type) {
            echo "Property '$path' was removed";
            echo PHP_EOL;
        }
    }
}

function parseValue($value): string
{
    if (is_array($value)) {
        return '[complex value]';
    }

    return is_string($value) ? $value : json_encode($value);
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
