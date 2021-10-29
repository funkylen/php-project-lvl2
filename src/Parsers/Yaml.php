<?php

namespace Differ\Parsers\Yaml;

use Symfony\Component\Yaml\Yaml;

function getFileContents(string $path): array
{
    return Yaml::parseFile($path);
}
