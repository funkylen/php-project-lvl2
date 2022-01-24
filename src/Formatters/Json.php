<?php

namespace Differ\Formatters\Json;

function getFormattedDiff(array $items): string
{
    return json_encode($items, JSON_PRETTY_PRINT);
}
