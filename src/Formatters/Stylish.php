<?php

namespace Differ\Formatters\Stylish;

use function Differ\DiffBuilder\getItems;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\isDiffList;

use const Differ\DiffBuilder\TYPE_ADDED;
use const Differ\DiffBuilder\TYPE_REMOVED;
use const Differ\DiffBuilder\TYPE_UNTOUCHED;

const PREFIX_ADDED = '+ ';
const PREFIX_REMOVED = '- ';
const PREFIX_UNTOUCHED = '  ';
const INDENT_LENGTH = 2;
const PREFIX_LENGTH = 2;

function get(array $diff): string
{
    return getFormattedString(prepareItems($diff));
}

function prepareItems($list)
{
    return array_reduce(getItems($list), function ($acc, $diff) {
        $key = getPrefix($diff) . getKey($diff);

        $value = getValue($diff);

        if (isDiffList($value)) {
            $acc[$key] = prepareItems($value);
        } else {
            $acc[$key] = $value;
        }

        return $acc;
    }, []);
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

function parseValue($value): string
{
    return is_string($value) ? $value : json_encode($value);
}

function getPrefix(array $diff): string
{
    switch (\Differ\DiffBuilder\getType($diff)) {
        case TYPE_ADDED:
            return PREFIX_ADDED;
        case TYPE_REMOVED:
            return PREFIX_REMOVED;
        case TYPE_UNTOUCHED:
        default:
            return PREFIX_UNTOUCHED;
    }
}

function hasPrefix($key): bool
{
    $prefix = substr($key, 0, PREFIX_LENGTH);
    return in_array($prefix, [PREFIX_ADDED, PREFIX_REMOVED, PREFIX_UNTOUCHED], true);
}
