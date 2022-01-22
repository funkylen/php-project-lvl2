<?php

namespace Differ\Formatters\Plain;

use function Differ\DiffBuilder\getChildren;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getOldValue;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\isAddedNode;
use function Differ\DiffBuilder\isDiff;
use function Differ\DiffBuilder\isRemovedNode;
use function Differ\DiffBuilder\isUntouchedNode;
use function Differ\DiffBuilder\isUpdatedNode;

function get(array $diff): string
{
    $prepared = prepareDiff($diff);
    return getFormattedString($prepared);
}

function prepareDiff(array $diff, string $rootPath = ''): array
{
    return array_reduce(getChildren($diff), function ($acc, $node) use ($rootPath) {
        $key = getKey($node);

        $path = $rootPath === '' ? $key : "$rootPath.$key";

        $value = getValue($node);

        if (isUntouchedNode($node)) {
            return isDiff($value) ? array_merge($acc, prepareDiff($value, $path)) : $acc;
        }

        return [
            ...$acc,
            makePlainDiffNode($path, $node)
        ];
    }, []);
}

function makePlainDiffNode(string $path, array $node): array
{
    return [
        'path' => $path,
        'node' => $node,
    ];
}

function getPath(array $plainDiffItem): string
{
    return $plainDiffItem['path'];
}

function getNode(array $plainDiffItem): array
{
    return $plainDiffItem['node'];
}

function getFormattedString(array $items): string
{
    return array_reduce($items, function ($formattedString, $plainDiffNode) {
        $path = getPath($plainDiffNode);
        $propertyInfo = "Property '{$path}'";

        $node = getNode($plainDiffNode);

        if (isAddedNode($node)) {
            $value = parseValue(getValue($node));
            return $formattedString . $propertyInfo . " was added with value: {$value}\n";
        }

        if (isRemovedNode($node)) {
            return $formattedString . $propertyInfo . " was removed\n";
        }

        if (isUpdatedNode($node)) {
            $oldValue = parseValue(getOldValue($node));
            $value = parseValue(getValue($node));
            return $formattedString . $propertyInfo . " was updated. From {$oldValue} to {$value}\n";
        }

        throw new \Exception('Undefined type');
    }, '');
}

/**
 * @param mixed $value
 * @return string
 */
function parseValue($value): string
{
    if (is_array($value)) {
        return '[complex value]';
    }

    if (is_string($value)) {
        return "'$value'";
    }

    return json_encode($value);
}
