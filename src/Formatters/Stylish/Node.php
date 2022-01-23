<?php

namespace Differ\Formatters\Stylish\Node;

const TYPE_ADDED = '__stylish_node__';
const TYPE_REMOVED = '__stylish_removed__';
const TYPE_UNTOUCHED = '__stylish_untouched__';

const PREFIX_ADDED = '+ ';
const PREFIX_REMOVED = '- ';
const PREFIX_UNTOUCHED = '  ';

function makeNode(string $type, string $key, $value, array $children = []): array
{
    return [
        'type' => $type,
        'prefix' => getPrefixForType($type),
        'key' => $key,
        'value' => $value,
        'children' => $children,
    ];
}

function getPrefixForType(string $type): string
{
    switch ($type) {
        case TYPE_ADDED:
            return PREFIX_ADDED;
        case TYPE_REMOVED:
            return PREFIX_REMOVED;
        case TYPE_UNTOUCHED:
            return PREFIX_UNTOUCHED;
        default:
            throw new \Exception('Undefined type!');
    }
}

function getType(array $node): string
{
    return $node['type'];
}

function getPrefix(array $node): string
{
    return $node['prefix'];
}

function getKey(array $node): string
{
    return $node['key'];
}

function getValue(array $node)
{
    return $node['value'];
}

function getChildren(array $node): array
{
    return $node['children'];
}

function hasChildren(array $node): bool
{
    return !empty(getChildren($node));
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
