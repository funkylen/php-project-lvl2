<?php

namespace Differ\Formatters\Stylish\Tree;

use function Differ\DiffBuilder\getChildren;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getNewValue;
use function Differ\DiffBuilder\getOldValue;
use function Differ\DiffBuilder\hasChildren;
use function Differ\DiffBuilder\isAddedNode;
use function Differ\DiffBuilder\isRemovedNode;
use function Differ\DiffBuilder\isUntouchedNode;
use function Differ\DiffBuilder\isUpdatedNode;
use function Differ\Formatters\Stylish\Node\makeNode;

use const Differ\Formatters\Stylish\Node\TYPE_UNTOUCHED;
use const Differ\Formatters\Stylish\Node\TYPE_REMOVED;
use const Differ\Formatters\Stylish\Node\TYPE_ADDED;

function makeTree(array $data): array
{
    return array_reduce($data, function ($acc, $node) {
        if (hasChildren($node)) {
            $children = makeTree(getChildren($node));
            $acc[] = makeNode(TYPE_UNTOUCHED, getKey($node), getOldValue($node), $children);
            return $acc;
        }

        if (isUpdatedNode($node)) {
            $acc[] = makeNode(TYPE_REMOVED, getKey($node), getOldValue($node));
            $acc[] = makeNode(TYPE_ADDED, getKey($node), getNewValue($node));
            return $acc;
        }

        if (isUntouchedNode($node)) {
            $acc[] = makeNode(TYPE_UNTOUCHED, getKey($node), getOldValue($node));
            return $acc;
        }

        if (isAddedNode($node)) {
            $acc[] = makeNode(TYPE_ADDED, getKey($node), getNewValue($node));
            return $acc;
        }

        if (isRemovedNode($node)) {
            $acc[] = makeNode(TYPE_REMOVED, getKey($node), getOldValue($node));
            return $acc;
        }

        return $acc;
    }, []);
}
