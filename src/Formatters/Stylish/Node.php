<?php

namespace Differ\Formatters\Stylish\Node;

const TYPE_ADDED = '__stylish_node__';
const TYPE_REMOVED = '__stylish_removed__';
const TYPE_UNTOUCHED = '__stylish_untouched__';

/**
 * @param string $type
 * @param string $key
 * @param mixed $value
 * @param array $children
 * @return array
 */
function makeNode(string $type, string $key, $value, array $children = []): array
{
    return [
        'type' => $type,
        'key' => $key,
        'value' => $value,
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
    return count(getChildren($node)) > 0;
}
