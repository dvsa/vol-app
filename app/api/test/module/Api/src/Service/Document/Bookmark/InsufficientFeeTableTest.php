<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\InsufficientFeeTable;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Insufficient Fee Table test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InsufficientFeeTableTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InsufficientFeeTable();
        $query = $bookmark->getQuery(['fee' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = m::mock(InsufficientFeeTable::class)
            ->makePartial();

        $bookmark->shouldReceive('getSnippet')
            ->with('TABLE_INSUFFICIENT_FEE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'description' => 'desc',
                'amount' => 100,
                'receivedAmount' => 20,
                'outstandingAmount' => 80

            ]
        );

        $expectedRowOne = [
            'COL1_BMK1' =>
                InsufficientFeeTable::RTF_BOLD_START . 'Fee description' . InsufficientFeeTable::RTF_BOLD_END,
            'COL2_BMK2' =>
                InsufficientFeeTable::RTF_BOLD_START . 'Amount' . InsufficientFeeTable::RTF_BOLD_END,
        ];

        $expectedRowTwo = [
            'COL1_BMK1' => 'desc',
            'COL2_BMK1' => "\'a3",
            'COL2_BMK2' => '100.00'
        ];

        $expectedRowThree = [
            'COL1_BMK1' => 'Amount RECEIVED',
            'COL2_BMK1' => "\'a3",
            'COL2_BMK2' => '20.00'
        ];

        $expectedRowFour = [
            'COL1_BMK1' =>
                InsufficientFeeTable::RTF_BOLD_START . 'BALANCE NOW DUE' . InsufficientFeeTable::RTF_BOLD_END,
            'COL2_BMK1' =>
                InsufficientFeeTable::RTF_BOLD_START . "\'a3" . InsufficientFeeTable::RTF_BOLD_END,
            'COL2_BMK2' =>
                InsufficientFeeTable::RTF_BOLD_START . '80.00' . InsufficientFeeTable::RTF_BOLD_END
        ];

        $parser = m::mock(RtfParser::class)
            ->makePartial()
            ->shouldReceive('replace')
            ->with('snippet', $expectedRowOne)
            ->andReturn('foo')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $expectedRowTwo)
            ->andReturn('bar')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $expectedRowThree)
            ->andReturn('baz')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $expectedRowFour)
            ->andReturn('cake')
            ->once()
            ->getMock();

        $bookmark->setParser($parser);

        $this->assertEquals('foobarbazcake', $bookmark->render());
    }

    public function testRenderWithEmptyData(): void
    {
        $bookmark = m::mock(InsufficientFeeTable::class)->makePartial();
        $bookmark->setData([]);
        $this->assertEquals('', $bookmark->render());
    }
}
