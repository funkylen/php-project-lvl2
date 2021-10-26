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

    if ($args['--format']) {
        $firstFilePath = $args['<firstFile>'];
        $secondFilePath = $args['<secondFile>'];

        genDiff($firstFilePath, $secondFilePath);
    }
}
