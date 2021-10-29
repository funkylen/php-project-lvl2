<?php

namespace Differ\Differ;

use Exception;

use function Differ\Parsers\Json\getFileContents as JsonGetFileContents;
use function Differ\Parsers\Yaml\getFileContents as YamlGetFileContents;

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

function makeAdded(string $key, $value): array
{
    return [
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeRemoved(string $key, $value): array
{
    return [
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeUntouched(string $key, $value): array
{
    return [
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
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

function generateDiffString(array $items): string
{
    $diff = "{\n";

    foreach ($items as $item) {
        switch (getType($item)) {
            case TYPE_ADDED:
                $diff .= '  + ';
                break;
            case TYPE_REMOVED:
                $diff .= '  - ';
                break;
            case TYPE_UNTOUCHED:
                $diff .= '    ';
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

function getKeySortedFileContents(string $path): array
{
    $content = getFileContents($path);

    ksort($content);

    return $content;
}

function genDiff(string $path1, string $path2): string
{
    $firstFileContent = getKeySortedFileContents($path1);
    $secondFileContent = getKeySortedFileContents($path2);
    $mergedContent = array_merge($firstFileContent, $secondFileContent);

    foreach ($mergedContent as $key => $value) {
        if (!isset($firstFileContent[$key])) {
            $data[] = makeAdded($key, $value);
        } elseif (!isset($secondFileContent[$key])) {
            $data[] = makeRemoved($key, $value);
        } elseif ($value === $firstFileContent[$key] && $value === $secondFileContent[$key]) {
            $data[] = makeUntouched($key, $value);
        } elseif ($value !== $firstFileContent[$key] && $value === $secondFileContent[$key]) {
            $data[] = makeRemoved($key, $firstFileContent[$key]);
            $data[] = makeAdded($key, $secondFileContent[$key]);
        } else {
            throw new Exception('Error: undefined type');
        }
    }

    return generateDiffString($data);
}
