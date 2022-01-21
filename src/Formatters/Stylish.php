<?php

namespace Differ\Formatters\Stylish;

use function Differ\DiffBuilder\getChildren;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getOldValue;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\isAddedNode;
use function Differ\DiffBuilder\isDiff;
use function Differ\DiffBuilder\isRemovedNode;
use function Differ\DiffBuilder\isUntouchedNode;
use function Differ\DiffBuilder\isUpdatedNode;

const PREFIX_ADDED = '+ ';
const PREFIX_REMOVED = '- ';
const PREFIX_UNTOUCHED = '  ';

const INDENT_LENGTH = 2;
const PREFIX_LENGTH = 2;

function get(array $diff): string
{
    return getFormattedString(prepareDiff($diff));
}

function prepareDiff(array $diff): array
{
    return array_reduce(getChildren($diff), function ($acc, $node) {
        if (isUpdatedNode($node)) {
            $removedValueKey = PREFIX_REMOVED . getKey($node);
            $acc[$removedValueKey] = getOldValue($node);

            $addedValueKey = PREFIX_ADDED . getKey($node);
            $acc[$addedValueKey] = getValue($node);

            return $acc;
        }

        $key = getPrefix($node) . getKey($node);

        $value = getValue($node);

        $acc[$key] = isUntouchedNode($node) && isDiff($value)
            ? prepareDiff($value)
            : $value;

        return $acc;
    }, []);
}

function getPrefix(array $node): string
{
    if (isAddedNode($node)) {
        return PREFIX_ADDED;
    }

    if (isRemovedNode($node)) {
        return PREFIX_REMOVED;
    }

    return PREFIX_UNTOUCHED;
}

function getFormattedString(array $items, int $depth = 1): string
{
    $formattedString = "{\n";

    foreach ($items as $key => $value) {
        $offsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * $depth - PREFIX_LENGTH;
        $formattedString .= str_repeat(' ', $offsetLength);

        if (!hasPrefix($key)) {
            $formattedString .= PREFIX_UNTOUCHED;
        }

        $formattedString .= "$key: ";

        if (is_array($value)) {
            $formattedString .= getFormattedString($value, $depth + 1);
        } else {
            $formattedString .= parseValue($value) . "\n";
        }
    }

    if ($depth > 1) {
        $endParenthesisOffsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * ($depth - 1);
        $formattedString .= str_repeat(' ', $endParenthesisOffsetLength);
    }

    $formattedString .= "}\n";

    return $formattedString;
}

function hasPrefix(string $key): bool
{
    $prefix = substr($key, 0, PREFIX_LENGTH);
    return in_array($prefix, [PREFIX_ADDED, PREFIX_REMOVED, PREFIX_UNTOUCHED], true);
}

/**
 * @param mixed $value
 * @return string
 */
function parseValue($value): string
{
    return is_string($value) ? $value : json_encode($value);
}
