<?php

namespace Differ\Differ;

use function Differ\Diff\Tree\makeTree;
use function Differ\Parsers\parseJson;
use function Differ\Parsers\parseYaml;
use function Differ\Formatter\getFormattedDiff;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    $firstFileContent = getParsedFileContents($path1);
    $secondFileContent = getParsedFileContents($path2);

    $diff = makeTree($firstFileContent, $secondFileContent);

    $formattedString = getFormattedDiff($diff, $format);

    return rtrim($formattedString, "\n ");
}

function getParsedFileContents(string $path): array
{
    $content = file_get_contents($path);

    if ($content === false) {
        throw new \Exception("Can't read file contents :(");
    }

    $extension = pathinfo($path, PATHINFO_EXTENSION);

    return parse($content, $extension);
}

function parse(string $content, string $extension): array
{
    if ($extension === 'json') {
        return parseJson($content);
    }

    if (in_array($extension, ['yml', 'yaml'], true)) {
        return parseYaml($content);
    }

    throw new \Exception('undefined format');
}
