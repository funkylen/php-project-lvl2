<?php

namespace Differ\Differ;

use Exception;

use Symfony\Component\Yaml\Yaml;
use function Differ\functions\array_merge_recursive_distinct;
use function Differ\functions\is_multidimensional_array;
use function Differ\Parsers\Json\getFileContents as JsonGetFileContents;
use function Differ\Parsers\Yaml\getFileContents as YamlGetFileContents;

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

function makeAdded(string $key, $value, int $depth = 0, string $parentKey = null): array
{
    return [
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
        'depth' => $depth,
        'parentKey' => $parentKey,
    ];
}

function makeRemoved(string $key, $value, int $depth = 0, string $parentKey = null): array
{
    return [
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
        'depth' => $depth,
        'parentKey' => $parentKey,
    ];
}

function makeUntouched(string $key, $value, int $depth = 0, string $parentKey = null): array
{
    return [
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
        'depth' => $depth,
        'parentKey' => $parentKey,
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
    return $item['value'];
}

function getDepth(array $item): int
{
    return $item['depth'];
}

function getParentKey(array $item): ?string
{
    return $item['parentKey'] ?? null;
}

function getSymbolForType(array $item): string
{
    switch (getType($item)) {
        case TYPE_ADDED:
            return '+';
        case TYPE_REMOVED:
            return '-';
    }
    return '';
}

function generateDiffString(array $items): string
{
    $diff = "{\n";

    $data = [];

    foreach ($items as $item) {
    }

    foreach ($items as $item) {
        $diff .= str_repeat(' ', getDepth($item) . 2);
        switch (getType($item)) {
            case TYPE_ADDED:
                $diff .= '+ ';
                break;
            case TYPE_REMOVED:
                $diff .= '- ';
                break;
            case TYPE_UNTOUCHED:
                $diff .= '  ';
                break;
        }

        $value = getValue($item);
        $parsedValue = is_string($value) ? $value : json_encode($value);

        $diff .= getKey($item) . ': ' . $parsedValue . "\n";
    }

    $diff .= "}\n";

    return $diff;
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
    $mergedContent = array_merge_recursive_distinct($firstFileContent, $secondFileContent);

    $data = iter($mergedContent, $firstFileContent, $secondFileContent, 0, []);

    usort($data, static function ($a, $b) {
        if (getDepth($a) !== getDepth($b)) {
            return getDepth($a) < getDepth($b) ? -1 : 1;
        }

        if (getParentKey($a) !== getParentKey($b)) {
            return strcmp(getParentKey($a), getParentKey($b));
        }

        if (getKey($a) !== getKey($b)) {
            return strcmp(getKey($a), getKey($b));
        }

        if (getType($a) === TYPE_REMOVED) {
            return -1;
        }

        if (getType($a) === TYPE_ADDED) {
            return 1;
        }

        return 0;

    });

    return generateDiffString($data);
}

function iter($mergedContent, $firstFileContent, $secondFileContent, $depth, $acc, $parentKey = null): array
{
    ++$depth;

    if (!is_multidimensional_array($mergedContent)) {
        return makeDiff($mergedContent, $firstFileContent, $secondFileContent, $depth, $acc, $parentKey);
    }

    foreach ($mergedContent as $key => $value) {
        if (!is_array($value)) {
            $acc = makeDiffPlain($key, $firstFileContent, $secondFileContent, $depth, $acc, $parentKey);
        } elseif (!array_key_exists($key, $firstFileContent)) {
            $acc[] = makeAdded($key, $value, $depth, $parentKey);
        } elseif (!array_key_exists($key, $secondFileContent)) {
            $acc[] = makeRemoved($key, $value, $depth, $parentKey);
        } else {
            $acc = iter($value, $firstFileContent[$key], $secondFileContent[$key], $depth, $acc, $key);
        }
    }

    return $acc;
}

function makeDiff($mergedContent, $firstFileContent, $secondFileContent, $depth, $acc, $parentKey): array
{
    foreach ($mergedContent as $key => $value) {
        $acc = makeDiffPlain($key, $firstFileContent, $secondFileContent, $depth, $acc, $parentKey);
    }

    return $acc;
}

function makeDiffPlain($key, $firstFileContent, $secondFileContent, $depth = 0, $acc = [], $parentKey = null): array
{
    if (!array_key_exists($key, $firstFileContent)) {
        $acc[] = makeAdded($key, $secondFileContent[$key], $depth, $parentKey);
    } elseif (!array_key_exists($key, $secondFileContent)) {
        $acc[] = makeRemoved($key, $firstFileContent[$key], $depth, $parentKey);
    } elseif ($secondFileContent[$key] === $firstFileContent[$key]) {
        $acc[] = makeUntouched($key, $firstFileContent[$key], $depth, $parentKey);
    } else {
        $acc[] = makeRemoved($key, $firstFileContent[$key], $depth, $parentKey);
        $acc[] = makeAdded($key, $secondFileContent[$key], $depth, $parentKey);
    }

    return $acc;
}
