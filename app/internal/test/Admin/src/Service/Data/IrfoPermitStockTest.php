<?php

namespace AdminTest\Service\Data;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Service\Data\IrfoPermitStock;
use Mockery as m;

/**
 * Class IrfoPermitStock Test
 * @package AdminTest\Service
 */
class IrfoPermitStockTest extends MockeryTestCase
{
    /**
     * Holds the SUT
     *
     * @var \Admin\Service\Data\IrfoPermitStock
     */
    private $sut;

    /**
     * Setup the sut
     */
    protected function setUp()
    {
        $this->sut = new IrfoPermitStock();
    }

    public function testGetServiceName()
    {
        $this->assertEquals('IrfoPermitStock', $this->sut->getServiceName());
    }

    /**
     * @dataProvider fetchIrfoPermitStockListDataProvider
     * @param array $results
     * @param array $expected
     */
    public function testFetchIrfoPermitStockList($results, $expected)
    {
        $filters = ['filter' => 'value'];

        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient
            ->shouldReceive('get')
            ->once()
            ->andReturn(['Results' => $results, 'Count' => count($results)]);

        $this->sut->setRestClient($mockRestClient);

        $this->assertEquals(
            $expected,
            $this->sut->fetchIrfoPermitStockList($filters)
        );
    }

    /**
     * Data provider for fetchIrfoPermitStockList.
     *
     * @return array
     */
    public function fetchIrfoPermitStockListDataProvider()
    {
        return [
            // returns false for empty results
            [
                [],
                false
            ],
            // returns results if not empty
            [
                [
                    ['id' => 99]
                ],
                [
                    'Count' => 1,
                    'Results' => [
                        ['id' => 99]
                    ]
                ]
            ],
        ];
    }
}
