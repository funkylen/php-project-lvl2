<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private const FIXTURES_FOLDER = __DIR__ . '/fixtures/';

    public function dataProvider(): array
    {
        return array_map(fn($format) => [$format, self::FIXTURES_FOLDER . 'diff_' . $format], [
            'stylish',
            'plain',
            'json',
        ]);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGenDiffJson($format, $diffPath): void
    {
        $filePath1 = self::FIXTURES_FOLDER . 'file1.json';
        $filePath2 = self::FIXTURES_FOLDER . 'file2.json';

        $result = genDiff($filePath1, $filePath2, $format);

        $this->assertStringEqualsFile($diffPath, $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGenDiffYaml($format, $diffPath): void
    {
        $filePath1 = self::FIXTURES_FOLDER . 'file1.yaml';
        $filePath2 = self::FIXTURES_FOLDER . 'file2.yml';

        $result = genDiff($filePath1, $filePath2, $format);

        $this->assertStringEqualsFile($diffPath, $result);
    }
}
