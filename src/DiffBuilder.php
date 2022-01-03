<?php

namespace Differ\DiffBuilder;

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

function getDiff(array $array1, array $array2): array
{
    $mergedContent = array_merge($array1, $array2);

    $list = makeDiffList();

    foreach ($mergedContent as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $node = makeAdded($key, $value);
            $list = add($node, $list);
        } elseif (!array_key_exists($key, $array2)) {
            $node = makeRemoved($key, $value);
            $list = add($node, $list);
        } elseif (is_array($value)) {
            $node = makeUntouched($key, getDiff($array1[$key], $array2[$key]));
            $list = add($node, $list);
        } elseif ($array2[$key] === $array1[$key]) {
            $node = makeUntouched($key, $value);
            $list = add($node, $list);
        } else {
            $nodeRemoved = makeRemoved($key, $array1[$key]);
            $list = add($nodeRemoved, $list);

            $nodeAdded = makeAdded($key, $array2[$key]);
            $list = add($nodeAdded, $list);
        }
    }

    return $list;
}

function makeDiffList(): array
{
    return [
        'isDiff' => true,
        'items' => [],
    ];
}

function isDiffList($list): bool
{
    if (!is_array($list)) {
        return false;
    }

    return array_key_exists('isDiff', $list) && $list['isDiff'] === true;
}

function getItems(array $list)
{
    return $list['items'];
}

function add($item, $list): array
{
    $list['items'][] = $item;

    usort($list['items'], fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $list;
}

function makeAdded(string $key, $value): array
{
    return [
        'isDiffItem' => true,
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeRemoved(string $key, $value): array
{
    return [
        'isDiffItem' => true,
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeUntouched(string $key, $value): array
{
    return [
        'isDiffItem' => true,
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
    ];
}

function isDiffItem($item): bool
{
    if (!is_array($item)) {
        return false;
    }

    return array_key_exists('isDiffItem', $item) && $item['isDiffItem'] === true;
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
