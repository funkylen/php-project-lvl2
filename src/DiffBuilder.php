<?php

namespace Differ\DiffBuilder;

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

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

function makeAdded(string $key, $value): array
{
    return [
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => is_array($value) ? makeUntouchedRecursive($key, $value) : $value,
    ];
}

function makeRemoved(string $key, $value): array
{
    return [
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => is_array($value) ? makeUntouchedRecursive($key, $value) : $value,
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
