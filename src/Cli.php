<?php

namespace Hexlet\Code\Cli;

use Docopt;

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
    foreach ($args as $k => $v) {
        echo $k . ': ' . json_encode($v) . PHP_EOL;
    }
}
