<?php

namespace Differ\DiffBuilder;

const DIFF_ID = '__diff__';
const NODE_ID = '__diff_node__';

const TYPE_ADDED = 'ADDED';
const TYPE_REMOVED = 'REMOVED';
const TYPE_UNTOUCHED = 'UNTOUCHED';

function getDiff(array $array1, array $array2): array
{
    $mergedContent = array_merge($array1, $array2);

    $list = makeDiff();

    foreach ($mergedContent as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $node = makeAdded($key, $value);
            $list = addNode($node, $list);
        } elseif (!array_key_exists($key, $array2)) {
            $node = makeRemoved($key, $value);
            $list = addNode($node, $list);
        } elseif (is_array($value)) {
            $node = makeUntouched($key, getDiff($array1[$key], $array2[$key]));
            $list = addNode($node, $list);
        } elseif ($array2[$key] === $array1[$key]) {
            $node = makeUntouched($key, $value);
            $list = addNode($node, $list);
        } else {
            $nodeRemoved = makeRemoved($key, $array1[$key]);
            $list = addNode($nodeRemoved, $list);

            $nodeAdded = makeAdded($key, $array2[$key]);
            $list = addNode($nodeAdded, $list);
        }
    }

    return $list;
}

function makeDiff(): array
{
    return [
        DIFF_ID,
        'items' => [],
    ];
}

function isDiff($diff): bool
{
    if (!is_array($diff) || !array_key_exists(0, $diff)) {
        return false;
    }

    return $diff[0] === DIFF_ID;
}

function getItems(array $diff)
{
    return $diff['items'];
}

function addNode(array $node, array $diff): array
{
    if (!isNode($node)) {
        throw new \Exception('You can add only diff nodes!');
    }

    $diff['items'][] = $node;

    usort($diff['items'], fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $diff;
}

function makeAdded(string $key, $value): array
{
    return [
        NODE_ID,
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeRemoved(string $key, $value): array
{
    return [
        NODE_ID,
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

function makeUntouched(string $key, $value): array
{
    return [
        NODE_ID,
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
    ];
}

function isNode($node): bool
{
    if (!is_array($node) || !array_key_exists(0, $node)) {
        return false;
    }

    return $node[0] === NODE_ID;
}

function getType(array $node): string
{
    return $node['type'];
}

function getKey(array $node): string
{
    return $node['key'];
}

function getValue(array $node)
{
    return $node['value'];
}
