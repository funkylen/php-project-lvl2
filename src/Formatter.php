<?php

namespace Differ\Formatter;

use function Differ\Formatters\Stylish\get as getStylishDiff;
use function Differ\Formatters\Plain\get as getPlainDiff;

function getFormattedDiff(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return getStylishDiff($diff);
    }

    if ($format === 'plain') {
        return getPlainDiff($diff);
    }

    throw new \Exception("Undefined format: ${format}");
}
