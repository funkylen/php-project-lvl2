<?php

namespace Differ\Differ;

use function Differ\Diff\Tree\makeTree;
use function Differ\Formatter\getFormattedDiff;
use function Differ\FileReader\getFileContents;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    $firstFileContent = getFileContents($path1);
    $secondFileContent = getFileContents($path2);

    $diff = makeTree($firstFileContent, $secondFileContent);

    $formattedString = getFormattedDiff($diff, $format);

    return rtrim($formattedString, "\n ");
}
