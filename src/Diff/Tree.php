<?php

namespace Differ\Diff\Tree;

use function Differ\Diff\Node\getKey;
use function Differ\Diff\Node\makeNode;

use const Differ\Diff\Node\TYPE_ADDED;
use const Differ\Diff\Node\TYPE_REMOVED;
use const Differ\Diff\Node\TYPE_UPDATED;
use const Differ\Diff\Node\TYPE_UNTOUCHED;

/**
 * @param mixed $firstData
 * @param mixed $secondData
 * @return array
 */
function makeTree($firstData, $secondData): array
{
    $allKeys = array_keys(array_merge($firstData, $secondData));

    $sortResult = sort($allKeys);

    $nodes = array_map(
        fn($key) => identifyTypeAndMakeNode($key, $firstData, $secondData),
        $allKeys,
    );

    return $nodes;
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
