<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $content, string $type): array
{
    if ($type === 'json') {
        return parseJson($content);
    }

    if (in_array($type, ['yml', 'yaml'], true)) {
        return parseYaml($content);
    }

    throw new \Exception('Undefined parse type!');
}

function parseJson(string $content): array
{
    return json_decode($content, true);
}

function parseYaml(string $content): array
{
    return Yaml::parse($content);
}
