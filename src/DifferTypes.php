<?php

namespace Differ\DiffTypes;


const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

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

function getPrefix(array $item): string
{
    return $item['prefix'];
}