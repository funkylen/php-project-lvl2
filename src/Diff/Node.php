<?php

namespace Differ\Diff\Node;

const TYPE_ADDED = '__diff_type_added__';
const TYPE_REMOVED = '__diff_type_removed__';
const TYPE_UNTOUCHED = '__diff_type_untouched__';
const TYPE_UPDATED = '__diff_type_updated__';

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

function getOldValue(array $node)
{
    return $node['oldValue'];
}

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
    return !empty($node['children']);
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
