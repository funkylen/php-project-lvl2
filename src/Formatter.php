<?php

namespace Differ\Formatter;

use Differ\Formatters\Stylish\Formatter as StylishFormatter;
use Differ\Formatters\Plain\Formatter as PlainFormatter;
use Differ\Formatters\Json\Formatter as JsonFormatter;

function getFormattedDiff(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return StylishFormatter\get($diff);
    }

    if ($format === 'plain') {
        return PlainFormatter\get($diff);
    }

    if ($format === 'json') {
        return JsonFormatter\get($diff);
    }

    throw new \Exception("Undefined format: ${format}");
}
