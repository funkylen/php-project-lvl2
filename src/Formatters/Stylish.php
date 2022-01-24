<?php

namespace Differ\Formatters\Stylish\Formatter;

use function Differ\Diff\Node\getNewValue;
use function Differ\Diff\Node\getOldValue;
use function Differ\Diff\Node\isAddedNode;
use function Differ\Diff\Node\isRemovedNode;
use function Differ\Diff\Node\isUntouchedNode;
use function Differ\Diff\Node\isUpdatedNode;

const TYPE_ADDED = '__stylish_node__';
const TYPE_REMOVED = '__stylish_removed__';
const TYPE_UNTOUCHED = '__stylish_untouched__';

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
    switch (getType($node)) {
        case TYPE_ADDED:
            return PREFIX_ADDED;
        case TYPE_REMOVED:
            return PREFIX_REMOVED;
        case TYPE_UNTOUCHED:
            return PREFIX_UNTOUCHED;
        default:
            throw new \Exception('Undefined type!');
    }
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

function makeTree(array $data): array
{
    return array_reduce($data, function ($acc, $node) {
        if (hasChildren($node)) {
            $children = makeTree(getChildren($node));
            return [
                ...$acc,
                makeNode(TYPE_UNTOUCHED, getKey($node), getOldValue($node), $children),
            ];
        }

        if (isUpdatedNode($node)) {
            return [
                ...$acc,
                makeNode(TYPE_REMOVED, getKey($node), getOldValue($node)),
                makeNode(TYPE_ADDED, getKey($node), getNewValue($node)),
            ];
        }

        if (isUntouchedNode($node)) {
            return [
                ...$acc,
                makeNode(TYPE_UNTOUCHED, getKey($node), getOldValue($node)),
            ];
        }

        if (isAddedNode($node)) {
            return [
                ...$acc,
                makeNode(TYPE_ADDED, getKey($node), getNewValue($node)),
            ];
        }

        if (isRemovedNode($node)) {
            return [
                ...$acc,
                makeNode(TYPE_REMOVED, getKey($node), getOldValue($node)),
            ];
        }

        return $acc;
    }, []);
}

/**
 * @param string $type
 * @param string $key
 * @param mixed $value
 * @param array $children
 * @return array
 */
function makeNode(string $type, string $key, $value, array $children = []): array
{
    return [
        'type' => $type,
        'key' => $key,
        'value' => $value,
        'children' => $children,
    ];
}

function getType(array $node): string
{
    return $node['type'];
}

function getKey(array $node): string
{
    return $node['key'];
}

function getValue(array $node)
{
    return $node['value'];
}

function getChildren(array $node): array
{
    return $node['children'];
}

function hasChildren(array $node): bool
{
    return count(getChildren($node)) > 0;
}
