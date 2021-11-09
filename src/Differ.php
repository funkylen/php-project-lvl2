<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\Json\getFileContents as JsonGetFileContents;
use function Differ\Parsers\Yaml\getFileContents as YamlGetFileContents;

use function Differ\DiffTypes\makeAdded;
use function Differ\DiffTypes\makeRemoved;
use function Differ\DiffTypes\makeUntouched;
use function Differ\DiffTypes\getKey;
use function Differ\DiffTypes\getValue;
use function Differ\DiffTypes\getPrefix;

function genDiff(string $path1, string $path2): string
{
    $firstFileContent = getFileContents($path1);
    $secondFileContent = getFileContents($path2);

    $diff = getDiff($firstFileContent, $secondFileContent);

    return generateDiffString($diff);
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

function getDiff(array $array1, array $array2): array
{
    $mergedContent = array_merge($array1, $array2);

    $diff = [];

    foreach ($mergedContent as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $diff[] = makeAdded($key, $value);
        } elseif (!array_key_exists($key, $array2)) {
            $diff[] = makeRemoved($key, $value);
        } elseif (is_array($value)) {
            $diff[] = makeUntouched($key, getDiff($array1[$key], $array2[$key]));
        } elseif ($array2[$key] === $array1[$key]) {
            $diff[] = makeUntouched($key, $value);
        } else {
            $diff[] = makeRemoved($key, $array1[$key]);
            $diff[] = makeAdded($key, $array2[$key]);
        }
    }

    usort($diff, fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $diff;
}

function generateDiffString(array $diff): string
{
    ob_start();
    iter($diff);
    return ob_get_clean();
}

function iter(array $diff, int $depth = 1)
{
    $indentLength = 2;
    $prefixLength = 2;

    echo '{';
    echo PHP_EOL;

    foreach ($diff as $d) {
        $offsetLength = ($indentLength + $prefixLength) * $depth - $prefixLength;
        echo str_repeat(' ', $offsetLength);
        echo getPrefix($d);
        echo getKey($d);
        echo ': ';

        $value = getValue($d);

        if (is_array($value)) {
            iter($value, $depth + 1);
        } else {
            $parsedValue = is_string($value) ? $value : json_encode($value);
            echo $parsedValue;
            echo PHP_EOL;
        }
    }

    if ($depth > 1) {
        $endParenthesisOffsetLength = ($indentLength + $prefixLength) * ($depth - 1);
        echo str_repeat(' ', $endParenthesisOffsetLength);
    }

    echo '}';
    echo PHP_EOL;
}
