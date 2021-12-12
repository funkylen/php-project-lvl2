<?php

namespace Differ\Cli;

use Docopt;

use function Differ\Differ\genDiff;

const DOC = <<<DOC
gendiff -h

Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help                     Show this screen
  -v --version                  Show version
  --format <fmt>                Report format [default: stylish]

DOC;

const PARAMS = [
    'version' => '0.0.1',
];

function run()
{
    $args = Docopt::handle(DOC, PARAMS);

    $firstFilePath = getAbsoluteFilePath($args['<firstFile>']);
    $secondFilePath = getAbsoluteFilePath($args['<secondFile>']);
    $format = strtolower($args['--format']);

    $diff = genDiff($firstFilePath, $secondFilePath, $format);
    print_r($diff);
}

function getAbsoluteFilePath(string $path): string
{
    if (strpos($path, '/') === 0) {
        return $path;
    }

    return __DIR__ . '/../' . $path;
}
