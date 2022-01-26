<?php

namespace Differ\Formatters\Stylish\Formatter;

use Differ\Diff;

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
    $formattedDiff = makeFormattedDiffFromTree($tree);
    return rtrim($formattedDiff, PHP_EOL);
}

function makeFormattedDiffFromTree(array $tree, int $depth = 1): string
{
    $content = array_map(function ($node) use ($depth) {
        $whitespace = str_repeat(' ', $depth * INDENT_LENGTH);
        $whitespaceWithPrefix = substr_replace($whitespace, getPrefix($node), -PREFIX_LENGTH);
        $keyPart = $whitespaceWithPrefix . getKey($node) . ': ';

        if (hasChildren($node)) {
            return $keyPart . makeFormattedDiffFromTree(getChildren($node), $depth + 1);
        }

        return $keyPart . parseValue(getValue($node), $depth + 1);
    }, $tree);

    return format($content, $depth);
}

function format(array $content, int $depth = 1): string
{
    $start = '{' . PHP_EOL;

    $endIndent = str_repeat(' ', ($depth - 1) * INDENT_LENGTH);
    $end = $endIndent . '}' . PHP_EOL;

    return $start . implode('', $content) . $end;
}

/**
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function parseValue($value, int $depth): string
{
    if (is_array($value)) {
        $content = array_map(function ($key, $value) use ($depth) {
            $whitespace = str_repeat(' ', $depth * INDENT_LENGTH);
            return $whitespace . $key . ': ' . parseValue($value, $depth + 1);
        }, array_keys($value), array_values($value));

        return format($content, $depth);
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
                makeNode(TYPE_UNTOUCHED, PREFIX_UNTOUCHED, getKey($node), Diff\getOldValue($node), $children),
            ];
        }

        $type = Diff\getType($node);

        if (Diff\TYPE_UPDATED === $type) {
            return [
                ...$acc,
                makeNode(TYPE_REMOVED, PREFIX_REMOVED, getKey($node), Diff\getOldValue($node)),
                makeNode(TYPE_ADDED, PREFIX_ADDED, getKey($node), Diff\getNewValue($node)),
            ];
        }

        if (Diff\TYPE_UNTOUCHED === $type) {
            return [
                ...$acc,
                makeNode(TYPE_UNTOUCHED, PREFIX_UNTOUCHED, getKey($node), Diff\getOldValue($node)),
            ];
        }

        if (Diff\TYPE_ADDED === $type) {
            return [
                ...$acc,
                makeNode(TYPE_ADDED, PREFIX_ADDED, getKey($node), Diff\getNewValue($node)),
            ];
        }

        if (Diff\TYPE_REMOVED === $type) {
            return [
                ...$acc,
                makeNode(TYPE_REMOVED, PREFIX_REMOVED, getKey($node), Diff\getOldValue($node)),
            ];
        }

        return $acc;
    }, []);
}

/**
 * @param string $type
 * @param string $prefix
 * @param string $key
 * @param mixed $value
 * @param array $children
 * @return array
 */
function makeNode(string $type, string $prefix, string $key, $value, array $children = []): array
{
    return [
        'type' => $type,
        'prefix' => $prefix,
        'key' => $key,
        'value' => $value,
        'children' => $children,
    ];
}

function getType(array $node): string
{
    return $node['type'];
}

function getPrefix(array $node): string
{
    return $node['prefix'];
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
