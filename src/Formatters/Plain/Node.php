<?php

namespace Differ\Formatters\Plain\Node;

const TYPE_ADDED = '__plain_added__';
const TYPE_REMOVED = '__plain_removed__';
const TYPE_UPDATED = '__plain_updated__';

/**
 * @param string $path
 * @param string $type
 * @param mixed $oldValue
 * @param mixed $newValue
 * @return array
 */
function makeNode(string $path, string $type, $oldValue, $newValue): array
{
    return [
        'path' => $path,
        'type' => $type,
        'oldValue' => $oldValue,
        'newValue' => $newValue,
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

function getOldValue(array $node)
{
    return $node['oldValue'];
}

function getNewValue(array $node)
{
    return $node['newValue'];
}

function isAddedNode(array $node): bool
{
    return getType($node) === TYPE_ADDED;
}

function isRemovedNode(array $node): bool
{
    return getType($node) === TYPE_REMOVED;
}

function isUpdatedNode(array $node): bool
{
    return getType($node) === TYPE_UPDATED;
}
