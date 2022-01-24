<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $path, $extension): array
{
    if ($extension === 'json') {
        return parseJson($path);
    }

    if (in_array($extension, ['yml', 'yaml'], true)) {
        return parseYaml($path);
    }

    throw new \Exception('undefined format');
}

function parseJson(string $content): array
{
    return json_decode($content, true);
}

function parseYaml(string $content): array
{
    return Yaml::parse($content);
}
