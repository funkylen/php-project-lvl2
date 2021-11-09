<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\Json\getFileContents as JsonGetFileContents;
use function Differ\Parsers\Yaml\getFileContents as YamlGetFileContents;

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';
const TYPE_NODE = 'NODE';

function makeAdded(string $key, $value): array
{
    return [
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => is_array($value) ? makeUntouchedRecursive($key, $value) : $value,
        'prefix' => '+ ',
    ];
}

function makeRemoved(string $key, $value): array
{
    return [
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => is_array($value) ? makeUntouchedRecursive($key, $value) : $value,
        'prefix' => '- ',
    ];
}

function makeUntouched(string $key, $value): array
{
    return [
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
        'prefix' => '  ',
    ];
}

function makeUntouchedRecursive(string $key, array $value): array
{
    $data = [];

    foreach ($value as $k => $v) {
        if (!is_array($v)) {
            $data[] = makeUntouched($k, $v);
        } else {
            $data[] = makeUntouched($k, makeUntouchedRecursive($k, $v));
        }
    }

    return $data;
}

function makeNode(string $key, array $children): array
{
    return [
        'type' => TYPE_NODE,
        'key' => $key,
        'children' => $children,
        'prefix' => '  ',
    ];
}


function getType(array $item): string
{
    return $item['type'];
}

function getKey(array $item): string
{
    return $item['key'];
}

function getValue(array $item)
{
    if (getType($item) === TYPE_NODE) {
        return null;
    }

    return $item['value'];
}

function getPrefix(array $item): string
{
    return $item['prefix'];
}

function getChildren(array $item): array
{
    if (getType($item) !== TYPE_NODE) {
        return [];
    }

    return $item['children'];
}

function getFileContents(string $path): array
{
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    if ($extension === 'json') {
        return JsonGetFileContents($path);
    }

    if (in_array($extension, ['yml', 'yaml'])) {
        return YamlGetFileContents($path);
    }

    throw new Exception('undefined format');
}

function genDiff(string $path1, string $path2): string
{
    $firstFileContent = getFileContents($path1);
    $secondFileContent = getFileContents($path2);

    $diff = getDiff($firstFileContent, $secondFileContent);

    return getOutput($diff);
}

function getOutput(array $diff): string
{
    ob_start();

    iter($diff);

    return ob_get_clean();
}

function iter(array $diff, int $depth = 1)
{
    echo '{';
    echo PHP_EOL;

    $offsetLength = 2;
    $prefixLength = 2;

    foreach ($diff as $d) {
        echo str_repeat(' ', ($offsetLength + $prefixLength) * $depth - $prefixLength);
        echo getPrefix($d);
        echo getKey($d);
        echo ': ';

        if (getType($d) === TYPE_NODE) {
            iter(getChildren($d), $depth + 1);
        } else {
            $value = getValue($d);

            if (is_array($value)) {
                iter($value, $depth + 1);
            } else {

                $parsedValue = is_string($value) ? $value : json_encode($value);
                echo $parsedValue;
                echo PHP_EOL;
            }
        }
    }

    $offset = ($offsetLength + $prefixLength) * ($depth - 1);
    if ($offset > 0) {
        echo str_repeat(' ', $offset);
    }
    echo '}';
    echo PHP_EOL;
}

function getDiff(array $array1, array $array2): array
{
    $mergedContent = array_merge($array1, $array2);

    $diff = [];

    foreach ($mergedContent as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $diff[] = makeAdded($key, $value);
        } elseif (!array_key_exists($key, $array2)) {
            $diff[] = makeRemoved($key, $value);
        } elseif (!is_array($value)) {
            if ($array2[$key] === $array1[$key]) {
                $diff[] = makeUntouched($key, $value);
            } else {
                $diff[] = makeRemoved($key, $array1[$key]);
                $diff[] = makeAdded($key, $array2[$key]);
            }
        } else {
            $diff[] = makeNode($key, getDiff($array1[$key], $array2[$key]));
        }
    }

    usort($diff, fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $diff;
}
