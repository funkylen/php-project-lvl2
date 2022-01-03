<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiffStylishJson(): void
    {
        $filePath1 = __DIR__ . '/fixtures/json/file1.json';   
        $filePath2 = __DIR__ . '/fixtures/json/file2.json';   
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffStylishYaml(): void
    {
        $filePath1 = __DIR__ . '/fixtures/yaml/file1.yaml';   
        $filePath2 = __DIR__ . '/fixtures/yaml/file2.yml';   
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffPlainJson(): void
    {
        $filePath1 = __DIR__ . '/fixtures/json/file1.json';
        $filePath2 = __DIR__ . '/fixtures/json/file2.json';
        $result = genDiff($filePath1, $filePath2, 'plain') ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff_plain';
        $this->assertStringEqualsFile($diffPath, $result);
    }

    public function testGenDiffPlainYaml(): void
    {
        $filePath1 = __DIR__ . '/fixtures/yaml/file1.yaml';
        $filePath2 = __DIR__ . '/fixtures/yaml/file2.yml';
        $result = genDiff($filePath1, $filePath2, 'plain') ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff_plain';
        $this->assertStringEqualsFile($diffPath, $result);
    }
}
