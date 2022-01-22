<?php

namespace Differ\FileReader;

use Differ\Parsers\Json;
use Differ\Parsers\Yaml;

function getFileContents(string $path): array
{
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    if ($extension === 'json') {
        return Json\getFileContents($path);
    }

    if (in_array($extension, ['yml', 'yaml'], true)) {
        return Yaml\getFileContents($path);
    }

    throw new \Exception('undefined format');
}