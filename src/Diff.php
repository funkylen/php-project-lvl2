<?php

namespace Differ\Diff;

use function Functional\sort;

const TYPE_ADDED = '__diff_type_added__';
const TYPE_REMOVED = '__diff_type_removed__';
const TYPE_UNTOUCHED = '__diff_type_untouched__';
const TYPE_UPDATED = '__diff_type_updated__';

/**
 * @param mixed $firstData
 * @param mixed $secondData
 * @return array
 */
function makeTree($firstData, $secondData): array
{
    $mergedKeys = array_keys(array_merge($firstData, $secondData));

    $sortedKeys = sort($mergedKeys, fn($left, $right) => strcmp($left, $right));

    return array_map(
        fn($key) => identifyTypeAndMakeNode($key, $firstData, $secondData),
        $sortedKeys,
    );
}

/**
 * @param string $key
 * @param mixed $firstData
 * @param mixed $secondData
 * @return array
 */
function identifyTypeAndMakeNode(string $key, $firstData, $secondData): array
{
    if (!array_key_exists($key, $firstData)) {
        return makeNode(TYPE_ADDED, $key, null, $secondData[$key]);
    }

    if (!array_key_exists($key, $secondData)) {
        return makeNode(TYPE_REMOVED, $key, $firstData[$key], null);
    }

    if (is_array($secondData[$key]) && is_array($firstData[$key])) {
        $childDiff = makeTree($firstData[$key], $secondData[$key]);
        return makeNode(TYPE_UNTOUCHED, $key, $firstData[$key], $secondData[$key], $childDiff);
    }

    if ($secondData[$key] === $firstData[$key]) {
        return makeNode(TYPE_UNTOUCHED, $key, $firstData[$key], $secondData[$key]);
    }

    return makeNode(TYPE_UPDATED, $key, $firstData[$key], $secondData[$key]);
}


/**
 * @param string $type
 * @param string $key
 * @param mixed $oldValue
 * @param mixed $newValue
 * @param array $children
 * @return array
 */
function makeNode(string $type, string $key, $oldValue, $newValue, array $children = []): array
{
    return [
        'type' => $type,
        'key' => $key,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
        'children' => $children,
    ];
}

function getType(array $node): string
{
    return $node['type'];
}

function getKey(array $node): string
{
    return $node['key'];
}

/**
 * @param array $node
 * @return mixed
 */
function getOldValue(array $node)
{
    return $node['oldValue'];
}

/**
 * @param array $node
 * @return mixed
 */
function getNewValue(array $node)
{
    return $node['newValue'];
}

function getChildren(array $node): array
{
    return $node['children'];
}

function hasChildren(array $node): bool
{
    return count(getChildren($node)) > 0;
}

function isAddedNode(array $node): bool
{
    return getType($node) === TYPE_ADDED;
}

function isRemovedNode(array $node): bool
{
    return getType($node) === TYPE_REMOVED;
}

function isUntouchedNode(array $node): bool
{
    return getType($node) === TYPE_UNTOUCHED;
}

function isUpdatedNode(array $node): bool
{
    return getType($node) === TYPE_UPDATED;
}

