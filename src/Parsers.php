<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;

function parseJson(string $content): array
{
    return json_decode($content, true);
}

function parseYaml(string $content): array
{
    return Yaml::parse($content);
}
