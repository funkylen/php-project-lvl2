<?php

namespace Differ\DiffBuilder;

use Exception;

const DIFF = '__diff__';
const NODE = '__diff_node__';

const TYPE_ADDED = '__diff_type_added__';
const TYPE_REMOVED = '__diff_type_removed__';
const TYPE_UNTOUCHED = '__diff_type_untouched__';
const TYPE_UPDATED = '__diff_type_updated__';

/**
 * @param mixed $firstData
 * @param mixed $secondData
 * @return array
 * @throws Exception
 */
function getDiff($firstData, $secondData): array
{
    $mergedContent = array_merge($firstData, $secondData);

    $keys = array_keys($mergedContent);
    $values = array_values($mergedContent);

    $nodes = array_map(fn($key, $value) => makeNode($key, $value, $firstData, $secondData), $keys, $values);

    return array_reduce($nodes, fn($diff, $node) => addChild($node, $diff), makeDiff());
}

function makeDiff(): array
{
    return [
        'entity' => DIFF,
        'children' => [],
    ];
}

/**
 * @param string $key
 * @param mixed $value
 * @param mixed $firstData
 * @param mixed $secondData
 * @return array
 * @throws Exception
 */
function makeNode(string $key, $value, $firstData, $secondData): array
{
    if (!array_key_exists($key, $firstData)) {
        return makeAdded($key, $value);
    }

    if (!array_key_exists($key, $secondData)) {
        return makeRemoved($key, $value);
    }

    if (is_array($value) && is_array($firstData[$key])) {
        $childDiff = getDiff($firstData[$key], $secondData[$key]);
        return makeUntouched($key, $childDiff);
    }

    if ($secondData[$key] === $firstData[$key]) {
        return makeUntouched($key, $value);
    }

    return makeUpdated($key, $firstData[$key], $secondData[$key]);
}

/**
 * @param mixed $diff
 * @return bool
 */
function isDiff($diff): bool
{
    if (!is_array($diff) || !array_key_exists('entity', $diff)) {
        return false;
    }

    return $diff['entity'] === DIFF;
}

/**
 * @param array $diff
 * @return mixed
 * @throws Exception
 */
function getChildren(array $diff)
{
    return validateDiff($diff)['children'];
}

/**
 * @param array $node
 * @param array $diff
 * @return array
 * @throws Exception
 */
function addChild(array $node, array $diff): array
{
    $validatedNode = validateNode($node);
    $validatedDiff = validateDiff($diff);

    $newChildren = array_merge(getChildren($validatedDiff), [$validatedNode]);
    $sortedChildren = sortChildren($newChildren);

    return array_merge($diff, [
        'children' => $sortedChildren,
    ]);
}

/**
 * @param array $children
 * @return array
 * @throws Exception
 */
function sortChildren(array $children): array
{
    // TODO: Написать свою сортировку
    usort($children, fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $children;
}

/**
 * @param array $node
 * @return array
 * @throws Exception
 */
function validateNode(array $node): array
{
    if (!isNode($node)) {
        throw new Exception('Item is not node!');
    }

    return $node;
}

/**
 * @param array $diff
 * @return array
 * @throws Exception
 */
function validateDiff(array $diff): array
{
    if (!isDiff($diff)) {
        throw new Exception('Item is not diff!');
    }

    return $diff;
}

/**
 * @param string $key
 * @param mixed $value
 * @return array
 */
function makeAdded(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_ADDED,
        'key' => $key,
        'value' => $value,
    ];
}

/**
 * @param mixed $node
 * @return bool
 * @throws Exception
 */
function isAddedNode($node): bool
{
    $validatedNode = validateNode($node);
    return getType($validatedNode) === TYPE_ADDED;
}

/**
 * @param string $key
 * @param mixed $value
 * @return array
 */
function makeRemoved(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

/**
 * @param mixed $node
 * @return bool
 * @throws Exception
 */
function isRemovedNode($node): bool
{
    $validatedNode = validateNode($node);
    return getType($validatedNode) === TYPE_REMOVED;
}

/**
 * @param string $key
 * @param mixed $value
 * @return array
 */
function makeUntouched(string $key, $value): array
{
    return [
        'entity' => NODE,
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
    ];
}

/**
 * @param mixed $node
 * @return bool
 * @throws Exception
 */
function isUntouchedNode($node): bool
{
    $validatedNode = validateNode($node);
    return getType($validatedNode) === TYPE_UNTOUCHED;
}

/**
 * @param string $key
 * @param mixed $oldValue
 * @param mixed $newValue
 * @return array
 */
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

/**
 * @param mixed $node
 * @return bool
 * @throws Exception
 */
function isUpdatedNode($node): bool
{
    $validatedNode = validateNode($node);
    return getType($validatedNode) === TYPE_UPDATED;
}

/**
 * @param mixed $node
 * @return bool
 */
function isNode($node): bool
{
    if (!is_array($node) || !array_key_exists('entity', $node)) {
        return false;
    }

    return $node['entity'] === NODE;
}

/**
 * @param array $node
 * @return string
 * @throws Exception
 */
function getType(array $node): string
{
    $validatedNode = validateNode($node);
    return $validatedNode['type'];
}

/**
 * @param array $node
 * @return string
 * @throws Exception
 */
function getKey(array $node): string
{
    $validatedNode = validateNode($node);
    return $validatedNode['key'];
}

/**
 * @param array $node
 * @return mixed
 * @throws Exception
 */
function getValue(array $node)
{
    $validatedNode = validateNode($node);
    return $validatedNode['value'];
}

/**
 * @param array $node
 * @return mixed
 * @throws Exception
 */
function getOldValue(array $node)
{
    $validatedNode = validateNode($node);

    if (!isUpdatedNode($node)) {
        throw new Exception('Node type needs to be updated for get old value');
    }

    return $validatedNode['oldValue'];
}
