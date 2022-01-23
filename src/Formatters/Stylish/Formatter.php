<?php

namespace Differ\Formatters\Stylish\Formatter;

use function Differ\Formatters\Stylish\Node\getChildren;
use function Differ\Formatters\Stylish\Node\getKey;
use function Differ\Formatters\Stylish\Node\hasChildren;
use function Differ\Formatters\Stylish\Node\getValue;
use function Differ\Formatters\Stylish\Tree\makeTree;

const KEY_INDENT_LENGTH = 4;
const PREFIX_LENGTH = 2;

function get(array $diff): string
{
    $tree = makeTree($diff);
    return getFormattedString($tree);
}

function getFormattedString($tree, $depth = 1): string
{
    $start = '{' . PHP_EOL;

    $endIndent = str_repeat(' ', ($depth - 1) * 4);
    $end = $endIndent . '}' . PHP_EOL;

    $keyIndent = str_repeat(' ', $depth * KEY_INDENT_LENGTH);

    $content = array_map(function ($node) use ($keyIndent, $depth) {
        $keyPart = $keyIndent  . getKey($node) . ': ';

        if (hasChildren($node)) {
            return $keyPart . getFormattedString(getChildren($node), $depth + 1);
        }

        return $keyPart . parseValue(getValue($node), $depth + 1);
    }, $tree);

    return $start . implode('', $content) . $end;
}


function getFormattedStringClear($data, $depth = 1): string
{
    $start = '{' . PHP_EOL;

    $endIndent = str_repeat(' ', ($depth - 1) * 4);
    $end = $endIndent . '}' . PHP_EOL;

    $keyIndent = str_repeat(' ', $depth * 4);

    $content = array_map(function ($key, $value) use ($keyIndent, $depth) {
        return $keyIndent  . "$key: " . parseValue($value, $depth + 1);
    }, array_keys($data), array_values($data));

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
        return getFormattedStringClear($value, $depth);
    }

    return is_string($value) ? $value . PHP_EOL : json_encode($value) . PHP_EOL;
}

//function getFormattedString(array $diff, int $depth = 1): string
//{
//    $formattedString = array_reduce($diff, function ($diffStr, $node) use ($depth) {
//        $offsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * $depth - PREFIX_LENGTH;
//        $offsetPart = str_repeat(' ', $offsetLength);
//        $keyPart = getKey($node). ': ';
//
//        if (hasChildren($node)) {
//            $valuePart = getFormattedString(getChildren($node), $depth + 1);
//        }
//
//        $valuePart = getValue($node);
//
//
//        if (is_array($value)) {
//            $formattedString .= getFormattedString($value, $depth + 1);
//        } else {
//            $formattedString .= parseValue($value) . "\n";
//        }
//
//    }, '');
//
//    if ($depth > 1) {
//        $endParenthesisOffsetLength = (INDENT_LENGTH + PREFIX_LENGTH) * ($depth - 1);
//        $formattedString .= str_repeat(' ', $endParenthesisOffsetLength);
//    }
//
//    return "{\n" . $formattedString . "}\n";
//}