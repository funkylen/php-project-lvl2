<?php

namespace Differ\Formatter;

use Differ\Formatters\Stylish\Formatter as StylishFormatter;
use Differ\Formatters\Plain as PlainFormatter;
use Differ\Formatters\Json\Formatter as JsonFormatter;

function getFormattedDiff(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return StylishFormatter\getFormattedDiff($diff);
    }

    if ($format === 'plain') {
        return PlainFormatter\getFormattedDiff($diff);
    }

    if ($format === 'json') {
        return JsonFormatter\getFormattedDiff($diff);
    }

    throw new \Exception("Undefined format: ${format}");
}
