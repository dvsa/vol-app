<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\IsExecuted;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class IsExecutedTest
 * @package Dvsa\OlcsTest\Api\Service\NrFilter\Format
 */
class IsExecutedTest extends TestCase
{
    /**
     * Tests the filter
     *
     * @dataProvider filterProvider
     * @param $input
     * @param $expectedOutput
     */
    public function testFilter($input, $expectedOutput)
    {
        $sut = new IsExecuted();
        $this->assertEquals($expectedOutput, $sut->filter($input));
    }

    /**
     * data provider for testFilterProvider
     */
    public function filterProvider()
    {
        $sut = new IsExecuted();

        return [
            [
                ['imposedErrus' => []],
                ['imposedErrus' => []]
            ],
            [
                ['imposedErrus' => [0 => ['executed' => 'Yes']]],
                ['imposedErrus' => [0 => ['executed' => $sut::YES_EXECUTED_KEY]]]
            ],
            [
                ['imposedErrus' => [0 => ['executed' => 'No']]],
                ['imposedErrus' => [0 => ['executed' => $sut::NO_EXECUTED_KEY]]]
            ],
            [
                ['imposedErrus' => [0 => ['executed' => 'Unknown']]],
                ['imposedErrus' => [0 => ['executed' => $sut::UNKNOWN_EXECUTED_KEY]]]
            ]
        ];
    }
}
