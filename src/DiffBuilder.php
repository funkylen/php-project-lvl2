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

    return array_reduce($nodes, fn($diff, $node) => addNode($node, $diff), makeDiff());
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
    validateDiff($diff);
    return $diff['children'];
}

/**
 * @param array $node
 * @param array $diff
 * @return array
 * @throws Exception
 */
function addNode(array $node, array $diff): array
{
    validateNode($node);
    validateDiff($diff);

    $diff['children'][] = $node;

    usort($diff['children'], fn($a, $b) => strcmp(getKey($a), getKey($b)));

    return $diff;
}

/**
 * @param array $node
 * @return void
 * @throws Exception
 */
function validateNode(array $node)
{
    if (!isNode($node)) {
        throw new Exception('Item is not node!');
    }
}

/**
 * @param array $diff
 * @return void
 * @throws Exception
 */
function validateDiff(array $diff)
{
    if (!isDiff($diff)) {
        throw new Exception('Item is not diff!');
    }
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
    validateNode($node);
    return getType($node) === TYPE_ADDED;
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
    validateNode($node);
    return getType($node) === TYPE_REMOVED;
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
    validateNode($node);
    return getType($node) === TYPE_UNTOUCHED;
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
    validateNode($node);
    return getType($node) === TYPE_UPDATED;
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
    validateNode($node);
    return $node['type'];
}

/**
 * @param array $node
 * @return string
 * @throws Exception
 */
function getKey(array $node): string
{
    validateNode($node);
    return $node['key'];
}

/**
 * @param array $node
 * @return mixed
 * @throws Exception
 */
function getValue(array $node)
{
    validateNode($node);
    return $node['value'];
}

/**
 * @param array $node
 * @return mixed
 * @throws Exception
 */
function getOldValue(array $node)
{
    validateNode($node);

    if (!isUpdatedNode($node)) {
        throw new Exception('Node type needs to be updated for get old value');
    }

    return $node['oldValue'];
}
