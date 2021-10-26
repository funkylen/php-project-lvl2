<?php

namespace Differ\Differ;

use Exception;

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

function getValue(array $item): string
{
    return $item['value'];
}

function generateDiffString(array $items): string
{
    $diff = "{\n";

    foreach ($items as $item) {
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

        $diff .= getKey($item) . ': ' . getValue($item) . "\n";
    }

    $diff .= "}\n";

    return $diff;
}

function getJsonFileContents(string $path): array
{
    $content = file_get_contents($path);
    return json_decode($content, true);
}

function genDiff(string $path1, string $path2): string
{
    $firstFileContent = getJsonFileContents($path1);
    $secondFileContent = getJsonFileContents($path2);

    ksort($firstFileContent);
    ksort($secondFileContent);

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
