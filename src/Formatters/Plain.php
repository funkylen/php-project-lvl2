<?php

namespace Differ\Formatters\Plain;

use function Differ\DiffBuilder\getChildren;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getOldValue;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\getType;
use function Differ\DiffBuilder\isDiff;

use const Differ\DiffBuilder\TYPE_ADDED;
use const Differ\DiffBuilder\TYPE_REMOVED;
use const Differ\DiffBuilder\TYPE_UNTOUCHED;
use const Differ\DiffBuilder\TYPE_UPDATED;

function get(array $diff): string
{
    return getFormattedString(prepareDiff(($diff)));
}

function prepareDiff(array $diff, string $rootPath = ''): array
{
    return array_reduce(getChildren($diff), function ($acc, $node) use ($rootPath) {
        $key = getKey($node);

        $path = empty($rootPath) ? $key : "$rootPath.$key";

        $value = getValue($node);

        if (getType($node) === TYPE_UNTOUCHED) {
            return isDiff($value) ? array_merge($acc, prepareDiff($value, $path)) : $acc;
        }

        $acc[$path] = makePlainDiffNode($path, $node);

        return $acc;
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

function getFormattedString($items): string
{
    return array_reduce($items, function ($formattedString, $plainDiffNode) {
        $path = getPath($plainDiffNode);
        $formattedString .= "Property '{$path}'";

        $node = getNode($plainDiffNode);

        if (getType($node) === TYPE_ADDED) {
            $value = parseValue(getValue($node));
            $formattedString .= " was added with value: {$value}\n";
            return $formattedString;
        }

        if (getType($node) === TYPE_REMOVED) {
            $formattedString .= " was removed\n";
            return $formattedString;
        }

        if (getType($node) === TYPE_UPDATED) {
            $oldValue = parseValue(getOldValue($node));
            $value = parseValue(getValue($node));
            $formattedString .= " was updated. From {$oldValue} to {$value}\n";
            return $formattedString;
        }

        throw new \Exception('Undefined type');
    }, '');
}

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
