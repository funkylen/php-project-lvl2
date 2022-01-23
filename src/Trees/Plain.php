<?php

namespace Differ\Trees\Plain;

const NODE = '__plain_node__';
const COLLECTION = '__plain_collection__';

const TYPE_ADDED = '__plain_added__';
const TYPE_REMOVED = '__plain_removed__';
const TYPE_UPDATED = '__plain_updated__';

function makeDiff(): array
{
    return [
        'entityType' => COLLECTION,
        'children' => [],
    ];
}

function makeNode(string $path, string $type, string $value): array
{
    return [
        'entityType' => NODE,
        'path' => $path,
        'type' => $type,
        'value' => $value,
    ];
}

function getPath(array $node): string
{
    return $node['path'];
}

function getType(array $node): string
{
    return $node['type'];
}

function getValue(array $node): string
{
    return $node['value'];
}


function makeAddedNode(string $path, string $value): array
{
    return [
        'entityType' => NODE,
        'type' => TYPE_ADDED,
        'path' => $path,
        'value' => $value,
    ];
}

function isAddedNode(array $node): bool
{
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
        'entityType' => NODE,
        'type' => TYPE_REMOVED,
        'key' => $key,
        'value' => $value,
    ];
}

function isRemovedNode($node): bool
{
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
        'entityType' => NODE,
        'type' => TYPE_UNTOUCHED,
        'key' => $key,
        'value' => $value,
    ];
}

function isUntouchedNode(array $node): bool
{
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
        'entityType' => NODE,
        'type' => TYPE_UPDATED,
        'key' => $key,
        'oldValue' => $oldValue,
        'value' => $newValue,
    ];
}

function isUpdatedNode(array $node): bool
{
    return getType($node) === TYPE_UPDATED;
}
