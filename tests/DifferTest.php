<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiffJson(): void
    {
        $filePath1 = __DIR__ . '/fixtures/json/file1.json';   
        $filePath2 = __DIR__ . '/fixtures/json/file2.json';   
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffYaml(): void
    {
        $filePath1 = __DIR__ . '/fixtures/yaml/file1.yaml';   
        $filePath2 = __DIR__ . '/fixtures/yaml/file2.yml';   
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffPlainJson(): void
    {
        $filePath1 = __DIR__ . '/fixtures/json/plain_file1.json';
        $filePath2 = __DIR__ . '/fixtures/json/plain_file2.json';
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/plain_file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffPlainYaml(): void
    {
        $filePath1 = __DIR__ . '/fixtures/yaml/plain_file1.yaml';
        $filePath2 = __DIR__ . '/fixtures/yaml/plain_file2.yml';
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/plain_file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }
}
