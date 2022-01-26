<?php

namespace Differ\Formatters\Stylish\Formatter;

use Differ\Diff;

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

        $children = getChildren($node);
        if (count($children) > 0) {
            return $keyPart . makeFormattedDiffFromTree($children, $depth + 1);
        }

        return $keyPart . parseValue(getValue($node), $depth + 1);
    }, $tree);

    return wrap($content, $depth);
}

function wrap(array $content, int $depth = 1): string
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

        return wrap($content, $depth);
    }

    return is_string($value) ? $value . PHP_EOL : json_encode($value) . PHP_EOL;
}

function makeTree(array $data): array
{
    return array_reduce($data, function ($acc, $node) {
        if (Diff\hasChildren($node)) {
            $children = makeTree(Diff\getChildren($node));
            return [
                ...$acc,
                makeNode(PREFIX_UNTOUCHED, Diff\getKey($node), Diff\getOldValue($node), $children),
            ];
        }

        $type = Diff\getType($node);

        if (Diff\TYPE_UPDATED === $type) {
            return [
                ...$acc,
                makeNode(PREFIX_REMOVED, Diff\getKey($node), Diff\getOldValue($node)),
                makeNode(PREFIX_ADDED, Diff\getKey($node), Diff\getNewValue($node)),
            ];
        }

        if (Diff\TYPE_UNTOUCHED === $type) {
            return [
                ...$acc,
                makeNode(PREFIX_UNTOUCHED, Diff\getKey($node), Diff\getOldValue($node)),
            ];
        }

        if (Diff\TYPE_ADDED === $type) {
            return [
                ...$acc,
                makeNode(PREFIX_ADDED, Diff\getKey($node), Diff\getNewValue($node)),
            ];
        }

        if (Diff\TYPE_REMOVED === $type) {
            return [
                ...$acc,
                makeNode(PREFIX_REMOVED, Diff\getKey($node), Diff\getOldValue($node)),
            ];
        }

        return $acc;
    }, []);
}

/**
 * @param string $prefix
 * @param string $key
 * @param mixed $value
 * @param array $children
 * @return array
 */
function makeNode(string $prefix, string $key, $value, array $children = []): array
{
    return [
        'prefix' => $prefix,
        'key' => $key,
        'value' => $value,
        'children' => $children,
    ];
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
