<?php

namespace Differ\Formatters\Plain;

use function Differ\Diff\Node\getChildren;
use function Differ\Diff\Node\getKey;
use function Differ\Diff\Node\hasChildren;
use function Differ\Diff\Node\isAddedNode;
use function Differ\Diff\Node\isRemovedNode;
use function Differ\Diff\Node\isUntouchedNode;
use function Differ\Diff\Node\isUpdatedNode;

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
        $key = getKey($node);

        $path = $rootPath === '' ? $key : "$rootPath.$key";

        if (hasChildren($node)) {
            return array_merge($acc, makeTree(getChildren($node), $path));
        }

        if (isUntouchedNode($node)) {
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
    if (isAddedNode($node)) {
        return TYPE_ADDED;
    }
    if (isUpdatedNode($node)) {
        return TYPE_UPDATED;
    }
    if (isRemovedNode($node)) {
        return TYPE_REMOVED;
    }

    throw new \Exception('Undefined Type!');
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
