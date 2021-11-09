<?php

namespace Differ\Differ;

use Exception;

use function Differ\DiffBuilder\getDiff;
use function Differ\DiffStringGenerator\generateDiffString;
use function Differ\Parsers\Json\getFileContents as JsonGetFileContents;
use function Differ\Parsers\Yaml\getFileContents as YamlGetFileContents;

function genDiff(string $path1, string $path2): string
{
    $firstFileContent = getFileContents($path1);
    $secondFileContent = getFileContents($path2);

    $diff = getDiff($firstFileContent, $secondFileContent);

    return generateDiffString($diff);
}

function getFileContents(string $path): array
{
    $extension = pathinfo($path, PATHINFO_EXTENSION);

    if ($extension === 'json') {
        return JsonGetFileContents($path);
    }

    if (in_array($extension, ['yml', 'yaml'])) {
        return YamlGetFileContents($path);
    }

    throw new Exception('undefined format');
}
