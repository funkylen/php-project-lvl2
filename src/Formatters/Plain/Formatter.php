<?php

namespace Differ\Formatters\Plain\Formatter;

use function Differ\Formatters\Plain\Node\getNewValue;
use function Differ\Formatters\Plain\Node\getOldValue;
use function Differ\Formatters\Plain\Node\getPath;
use function Differ\Formatters\Plain\Node\isAddedNode;
use function Differ\Formatters\Plain\Node\isRemovedNode;
use function Differ\Formatters\Plain\Node\isUpdatedNode;
use function Differ\Formatters\Plain\Tree\makeTree;

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

        if (isAddedNode($node)) {
            $newValue = parseValue(getNewValue($node));
            return $propertyInfo . " was added with value: {$newValue}";
        }

        if (isRemovedNode($node)) {
            return $propertyInfo . " was removed";
        }

        if (isUpdatedNode($node)) {
            $oldValue = parseValue(getOldValue($node));
            $newValue = parseValue(getNewValue($node));
            return $propertyInfo . " was updated. From {$oldValue} to {$newValue}";
        }

        throw new \Exception('Undefined type');
    }, $tree);

    return implode(PHP_EOL, $content);
}

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
