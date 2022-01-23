<?php

namespace Differ\Formatters\Stylish\Formatter;

use function Differ\Formatters\Stylish\Node\getChildren;
use function Differ\Formatters\Stylish\Node\getKey;
use function Differ\Formatters\Stylish\Node\hasChildren;
use function Differ\Formatters\Stylish\Node\getValue;
use function Differ\Formatters\Stylish\Node\isAddedNode;
use function Differ\Formatters\Stylish\Node\isRemovedNode;
use function Differ\Formatters\Stylish\Node\isUntouchedNode;
use function Differ\Formatters\Stylish\Tree\makeTree;

const PREFIX_ADDED = '+ ';
const PREFIX_REMOVED = '- ';
const PREFIX_UNTOUCHED = '  ';

const INDENT_LENGTH = 4;
const PREFIX_LENGTH = 2;

function getFormattedDiff(array $diff): string
{
    $tree = makeTree($diff);
    return makeFormattedDiffFromTree($tree);
}

function format(array $content, int $depth = 1): string
{
    $start = '{' . PHP_EOL;

    $endIndent = str_repeat(' ', ($depth - 1) * 4);
    $end = $endIndent . '}' . PHP_EOL;

    return $start . implode('', $content) . $end;
}

function getIndentWithPrefix(int $depth, string $prefix = PREFIX_UNTOUCHED): string
{
    $whitespace = $depth * INDENT_LENGTH - PREFIX_LENGTH;

    return str_repeat(' ', $whitespace) . $prefix;
}

function makeFormattedDiffFromTree(array $tree, int $depth = 1): string
{
    $content = array_map(function ($node) use ($depth) {
        $keyPart = getIndentWithPrefix($depth, getPrefix($node)) . getKey($node) . ': ';

        if (hasChildren($node)) {
            return $keyPart . makeFormattedDiffFromTree(getChildren($node), $depth + 1);
        }

        return $keyPart . parseValue(getValue($node), $depth + 1);
    }, $tree);

    return format($content, $depth);
}

function getPrefix(array $node): string
{
    if (isAddedNode($node)) {
        return PREFIX_ADDED;
    }
    if (isRemovedNode($node)) {
        return PREFIX_REMOVED;
    }
    if (isUntouchedNode($node)) {
        return PREFIX_UNTOUCHED;
    }

    throw new \Exception('Undefined type!');
}

function makeFormattedDiffFromArray(array $data, int $depth = 1): string
{
    $content = array_map(function ($key, $value) use ($depth) {
        return getIndentWithPrefix($depth) . $key . ': ' . parseValue($value, $depth + 1);
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
        return makeFormattedDiffFromArray($value, $depth);
    }

    return is_string($value) ? $value . PHP_EOL : json_encode($value) . PHP_EOL;
}
