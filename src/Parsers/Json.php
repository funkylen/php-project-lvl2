<?php

namespace Differ\Parsers\Json;

function getFileContents(string $path): array
{
    $content = file_get_contents($path);

    if ($content === false) {
        throw new \Exception("Can't read file contents :(");
    }

    return json_decode($content, true);
}
