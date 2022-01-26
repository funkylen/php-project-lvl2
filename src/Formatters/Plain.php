<?php

namespace Differ\Formatters\Plain;

use Differ\Diff;

const TYPE_ADDED = '__plain_added__';
const TYPE_REMOVED = '__plain_removed__';
const TYPE_UPDATED = '__plain_updated__';

const COMPLEX_VALUE = '[complex value]';

function getFormattedDiff(array $diff): string
{
    $tree = makeTree($diff);
    return makeFormattedDiffFromTree($tree);
}

function makeFormattedDiffFromTree(array $tree): string
{
    $content = array_map(function ($node) {
        $path = getPath($node);
        $propertyInfo = "Property '{$path}'";

        $type = getType($node);

        if (TYPE_ADDED === $type) {
            $newValue = parseValue(getNewValue($node));
            return $propertyInfo . " was added with value: {$newValue}";
        }

        if (TYPE_REMOVED === $type) {
            return $propertyInfo . " was removed";
        }

        if (TYPE_UPDATED === $type) {
            $oldValue = parseValue(getOldValue($node));
            $newValue = parseValue(getNewValue($node));
            return $propertyInfo . " was updated. From {$oldValue} to {$newValue}";
        }

        throw new \Exception('Undefined type');
    }, $tree);

    return implode(PHP_EOL, $content);
}

/**
 * @param mixed $value
 * @return string
 */
function parseValue($value): string
{
    if (is_array($value)) {
        return COMPLEX_VALUE;
    }

    if (is_string($value)) {
        return "'$value'";
    }

    return json_encode($value);
}

function makeTree(array $data, string $rootPath = ''): array
{
    return array_reduce($data, function ($acc, $node) use ($rootPath) {
        $key = Diff\getKey($node);

        $path = $rootPath === '' ? $key : "$rootPath.$key";

        if (Diff\hasChildren($node)) {
            return array_merge($acc, makeTree(Diff\getChildren($node), $path));
        }

        if (Diff\TYPE_UNTOUCHED === Diff\getType($node)) {
            return $acc;
        }

        return [
            ...$acc,
            makeNode($path, identifyType($node), getOldValue($node), getNewValue($node))
        ];
    }, []);
}

function identifyType(array $node): string
{
    switch (Diff\getType($node)) {
        case Diff\TYPE_ADDED:
            return TYPE_ADDED;
        case Diff\TYPE_REMOVED:
            return TYPE_REMOVED;
        case Diff\TYPE_UPDATED:
            return TYPE_UPDATED;
        default:
            throw new \Exception('Undefined Type!');
    }
}

/**
 * @param string $path
 * @param string $type
 * @param mixed $oldValue
 * @param mixed $newValue
 * @return array
 */
function makeNode(string $path, string $type, $oldValue, $newValue): array
{
    return [
        'path' => $path,
        'type' => $type,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
    ];
}

function getPath(array $node): string
{
    return $node['path'];
}

function getType(array $node): string
{
    return $node['type'];
}

function getOldValue(array $node)
{
    return $node['oldValue'];
}

function getNewValue(array $node)
{
    return $node['newValue'];
}
