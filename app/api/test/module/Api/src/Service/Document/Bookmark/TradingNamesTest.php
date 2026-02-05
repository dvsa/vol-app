<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TradingNames;

/**
 * Trading Names test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TradingNamesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TradingNames();
        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertEquals(
            [
                'id' => 123,
                'bundle' => ['tradingNames']
            ],
            $query->getArrayCopy()
        );
    }

    public function testRenderWithNoTradingNames(): void
    {
        $bookmark = new TradingNames();
        $bookmark->setData(
            [
                'tradingNames' => null
            ]
        );

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithTradingNames(): void
    {
        $bookmark = new TradingNames();
        $bookmark->setData(
            [
                'tradingNames' => [
                    ['name' => 'tn1'],
                    ['name' => 'tn2'],
                    ['name' => 'tn3']
                ]
            ]
        );

        $this->assertEquals(
            'tn1, tn2, tn3',
            $bookmark->render()
        );
    }
}
