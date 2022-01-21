# File Differ
[![Actions Status](https://github.com/funkylen/php-project-lvl2/workflows/hexlet-check/badge.svg)](https://github.com/funkylen/php-project-lvl2/actions)
[![PHP CI](https://github.com/funkylen/php-project-lvl2/actions/workflows/workflow.yml/badge.svg)](https://github.com/funkylen/php-project-lvl2/actions/workflows/workflow.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/0ec72d5923b9b585b2f8/maintainability)](https://codeclimate.com/github/funkylen/php-project-lvl2/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/0ec72d5923b9b585b2f8/test_coverage)](https://codeclimate.com/github/funkylen/php-project-lvl2/test_coverage)

Get diff between two files.

## File formats
* JSON
* YAML

## Output formats
* Stylish (default)
* Plain
* JSON

## Requirements 
* PHP 7.4
* Composer 2.0

## Install and run
```shell
git clone git@github.com:funkylen/php-project-lvl2.git differ
cd differ
make install
./bin/gendiff --format <output_format> <first_file_path> <second_file_path>
```

## Work example
[![asciicast](https://asciinema.org/a/463240.svg)](https://asciinema.org/a/463240)