<?php

namespace Differ\Parsers\Json;

function getFileContents(string $path): array
{
    $content = file_get_contents($path);
    return json_decode($content, true);
}
