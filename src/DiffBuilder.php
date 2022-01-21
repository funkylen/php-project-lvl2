<?php

namespace Differ\DiffBuilder;

const DIFF = '__diff__';
const NODE = '__diff_node__';

const TYPE_ADDED = '__diff_type_added__';
const TYPE_REMOVED = '__diff_type_removed__';
const TYPE_UNTOUCHED = '__diff_type_untouched__';
const TYPE_UPDATED = '__diff_type_updated__';

function getDiff(array $array1, array $array2): array
{
    $mergedContent = array_merge($array1, $array2);

    $diff = makeDiff();

    foreach ($mergedContent as $key => $value) {
        if (!array_key_exists($key, $array1)) {
            $node = makeAdded($key, $value);
            $diff = addNode($node, $diff);
        } elseif (!array_key_exists($key, $array2)) {
            $node = makeRemoved($key, $value);
            $diff = addNode($node, $diff);
        } elseif (is_array($value)) {
            $childDiff = getDiff($array1[$key], $array2[$key]);
            $node = makeUntouched($key, $childDiff);
            $diff = addNode($node, $diff);
        } elseif ($array2[$key] === $array1[$key]) {
            $node = makeUntouched($key, $value);
            $diff = addNode($node, $diff);
        } else {
            $node = makeUpdated($key, $array1[$key], $array2[$key]);
            $diff = addNode($node, $diff);
        }
    }

    return $diff;
}

function makeDiff(): array
{
    return [
        'entity' => DIFF,
        'children' => [],
    ];
}

function isDiff($diff): bool
{
    if (!is_array($diff) || !array_key_exists('entity', $diff)) {
        return false;
    }

    return $diff['entity'] === DIFF;
}

function getChildren(array $diff)
{
    validateDiff($diff);
    return $diff['children'];
}

function addNode(array $node, array $diff): array
{
    validateNode($node);
    validateDiff($diff);

    $diff['children'][] = $node;

    usort($diff['children'], fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $diff;
}

function validateNode(array $node)
{
    if (!isNode($node)) {
        throw new \Exception('Item is not node!');
    }
}

function validateDiff(array $diff)
{
    if (!isDiff($diff)) {
        throw new \Exception('Item is not diff!');
    }
}

function makeAdded(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
    ];
}

function isAddedNode($node): bool
{
    validateNode($node);
    return getType($node) === TYPE_ADDED;
}

function makeRemoved(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

function isRemovedNode($node): bool
{
    validateNode($node);
    return getType($node) === TYPE_REMOVED;
}

function makeUntouched(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
    ];
}

function isUntouchedNode($node): bool
{
    validateNode($node);
    return getType($node) === TYPE_UNTOUCHED;
}

function makeUpdated(string $key, $oldValue, $newValue): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_UPDATED,
        'key' => $key,
        'oldValue' => $oldValue,
        'value' => $newValue,
    ];
}

function isUpdatedNode($node): bool
{
    validateNode($node);
    return getType($node) === TYPE_UPDATED;
}

function isNode($node): bool
{
    if (!is_array($node) || !array_key_exists('entity', $node)) {
        return false;
    }

    return $node['entity'] === NODE;
}

function getType(array $node): string
{
    validateNode($node);
    return $node['type'];
}

function getKey(array $node): string
{
    validateNode($node);
    return $node['key'];
}

function getValue(array $node)
{
    validateNode($node);
    return $node['value'];
}

function getOldValue(array $node)
{
    validateNode($node);

    if (!isUpdatedNode($node)) {
        throw new \Exception('Node type needs to be updated for get old value');
    }

    return $node['oldValue'];
}
