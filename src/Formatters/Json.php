<?php

namespace Differ\Formatters\Json;

function get(array $diff): string
{
    return getFormattedString($diff);
}

function getFormattedString(array $items)
{
    return json_encode($items, JSON_PRETTY_PRINT);
}
