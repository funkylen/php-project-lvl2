<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    public function testGenDiffPlain(): void
    {
        $filePath1 = __DIR__ . '/fixtures/file1.json';   
        $filePath2 = __DIR__ . '/fixtures/file2.json';   
        $result = genDiff($filePath1, $filePath2) ;

        $diffPath = __DIR__ . '/fixtures/file1_file2_diff';
        $this->assertStringEqualsFile($diffPath, $result);
    }
}
