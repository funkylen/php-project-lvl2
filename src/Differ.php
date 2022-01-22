<?php

namespace Differ\Differ;

use function Differ\DiffBuilder\getDiff;
use function Differ\Formatter\getFormattedDiff;
use function Differ\FileReader\getFileContents;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    $firstFileContent = getFileContents($path1);
    $secondFileContent = getFileContents($path2);

    $diff = getDiff($firstFileContent, $secondFileContent);

    $formattedString = getFormattedDiff($diff, $format);

    return rtrim($formattedString, "\n ");
}
