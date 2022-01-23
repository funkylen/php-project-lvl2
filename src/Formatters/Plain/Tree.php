<?php

namespace Differ\Formatters\Plain\Tree;

use function Differ\Diff\Node\getChildren;
use function Differ\Diff\Node\getKey;
use function Differ\Diff\Node\getNewValue;
use function Differ\Diff\Node\getOldValue;
use function Differ\Diff\Node\hasChildren;
use function Differ\Diff\Node\isAddedNode;
use function Differ\Diff\Node\isRemovedNode;
use function Differ\Diff\Node\isUntouchedNode;
use function Differ\Diff\Node\isUpdatedNode;
use function Differ\Formatters\Plain\Node\makeNode;

use const Differ\Formatters\Plain\Node\TYPE_ADDED;
use const Differ\Formatters\Plain\Node\TYPE_REMOVED;
use const Differ\Formatters\Plain\Node\TYPE_UPDATED;

function makeTree(array $data, string $rootPath = ''): array
{
    return array_reduce($data, function ($acc, $node) use ($rootPath) {
        $key = getKey($node);

        $path = $rootPath === '' ? $key : "$rootPath.$key";

        if (hasChildren($node)) {
            return array_merge($acc, makeTree(getChildren($node), $path));
        }

        if (isUntouchedNode($node)) {
            return $acc;
        }

        return [
            ...$acc,
            makeNode($path, identifyType($node), getOldValue($node), getNewValue($node))
        ];
    }, []);
}

function identifyType(array $node): string
{
    if (isAddedNode($node)) {
        return TYPE_ADDED;
    }
    if (isUpdatedNode($node)) {
        return TYPE_UPDATED;
    }
    if (isRemovedNode($node)) {
        return TYPE_REMOVED;
    }

    throw new \Exception('Undefined Type!');
}
