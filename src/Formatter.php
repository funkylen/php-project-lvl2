<?php

namespace Differ\Formatter;

use Differ\Formatters\Stylish;
use Differ\Formatters\Plain;
use Differ\Formatters\Json;

function getFormattedDiff(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return Stylish\get($diff);
    }

    if ($format === 'plain') {
        return Plain\get($diff);
    }

    if ($format === 'json') {
        return Json\get($diff);
    }

    throw new \Exception("Undefined format: ${format}");
}
