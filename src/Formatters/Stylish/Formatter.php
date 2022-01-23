<?php

namespace Differ\Formatters\Stylish\Formatter;

use function Differ\Formatters\Stylish\Node\getChildren;
use function Differ\Formatters\Stylish\Node\getKey;
use function Differ\Formatters\Stylish\Node\hasChildren;
use function Differ\Formatters\Stylish\Node\getValue;
use function Differ\Formatters\Stylish\Tree\makeTree;

const KEY_INDENT_LENGTH = 4;

function get(array $diff): string
{
    $tree = makeTree($diff);
    return makeFromTree($tree);
}

function format($content, $depth = 1): string
{
    $start = '{' . PHP_EOL;

    $endIndent = str_repeat(' ', ($depth - 1) * 4);
    $end = $endIndent . '}' . PHP_EOL;

    return $start . implode('', $content) . $end;
}

function getIndent(int $depth): string
{
    return str_repeat(' ', $depth * KEY_INDENT_LENGTH);
}

function makeFromTree(array $tree, int $depth = 1): string
{
    $content = array_map(function ($node) use ($depth) {
        $keyPart = getIndent($depth) . getKey($node) . ': ';

        if (hasChildren($node)) {
            return $keyPart . makeFromTree(getChildren($node), $depth + 1);
        }

        return $keyPart . parseValue(getValue($node), $depth + 1);
    }, $tree);

    return format($content, $depth);
}

function makeFromArray(array $data, int $depth = 1): string
{
    $content = array_map(function ($key, $value) use ($depth) {
        return getIndent($depth) . $key . ': ' . parseValue($value, $depth + 1);
    }, array_keys($data), array_values($data));

    return format($content, $depth);
}

/**
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function parseValue($value, int $depth): string
{
    if (is_array($value)) {
        return makeFromArray($value, $depth);
    }

    return is_string($value) ? $value . PHP_EOL : json_encode($value) . PHP_EOL;
}
