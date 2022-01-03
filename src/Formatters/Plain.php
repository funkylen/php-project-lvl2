<?php

namespace Differ\Formatters\Plain;

use function Differ\DiffBuilder\getItems;
use function Differ\DiffBuilder\getKey;
use function Differ\DiffBuilder\getValue;
use function Differ\DiffBuilder\getType;
use function Differ\DiffBuilder\isDiffList;
use const Differ\DiffBuilder\TYPE_ADDED;
use const Differ\DiffBuilder\TYPE_REMOVED;

const TYPE_UPDATED = 'UPDATED';

function get(array $diff)
{
    return getFormattedString(prepareItems(($diff)));
}

function prepareItems(array $list, string $rootPath = ''): array
{
    return array_reduce(getItems($list), function ($acc, $diff) use ($rootPath) {
        $key = getKey($diff);

        $path = empty($rootPath) ? $key : "$rootPath.$key";

        $value = getValue($diff);

        if (isDiffList($value)) {
            return array_merge($acc, prepareItems($value, $path));
        }

        if (!in_array(getType($diff), [TYPE_ADDED, TYPE_REMOVED], true)) {
            return $acc;
        }

        $diff = [
            'path' => $path,
            'value' => parseValue($value),
            'type' => getType($diff),
        ];

        $prevItem = end($acc);

        if ($prevItem && $prevItem['path'] === $path && $prevItem['type'] === TYPE_REMOVED) {
            $diff['type'] = TYPE_UPDATED;
            $diff['oldValue'] = $prevItem['value'];
        }

        $acc[$path] = $diff;

        return $acc;
    }, []);
}

function getFormattedString($items): string
{
    return array_reduce($items, function ($formattedString, $item) {
        $formattedString .= "Property '{$item['path']}'";

        switch ($item['type']) {
            case TYPE_ADDED:
                $formattedString .= " was added with value: {$item['value']}\n";
                return $formattedString;
            case TYPE_REMOVED:
                $formattedString .= " was removed\n";
                return $formattedString;
            case TYPE_UPDATED:
                $formattedString .= " was updated. From {$item['oldValue']} to {$item['value']}\n";
                return $formattedString;
            default:
                throw new \Exception('Undefined type');
        }
    }, '');
}

function parseValue($value): string
{
    if (is_array($value)) {
        return '[complex value]';
    }

    if (is_string($value)) {
        return "'$value'";
    }

    return json_encode($value);
}
