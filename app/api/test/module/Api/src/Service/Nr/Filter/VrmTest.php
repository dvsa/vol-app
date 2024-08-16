<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter;

use Dvsa\Olcs\Api\Service\Nr\Filter\Vrm;
use Dvsa\Olcs\Transfer\Filter\Vrm as TransferVrmFilter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class VrmTest
 * @package Dvsa\OlcsTest\Api\Service\Nr\Filter
 */
class VrmTest extends MockeryTestCase
{
    /**
     * test filter()
     *
     * @dataProvider dpFilterProvider
     * @param string $initialValue
     * @param string $expectedResult
     */
    public function testFilter($initialValue, $expectedResult)
    {
        $value = ['vrm' => $initialValue];
        $expected = ['vrm' => $expectedResult];

        $mockTransferFilter = m::mock(TransferVrmFilter::class);
        $mockTransferFilter->shouldReceive('filter')->with($initialValue)->andReturn($expectedResult);

        $sut = new Vrm();
        $sut->setVrmFilter($mockTransferFilter);

        $this->assertEquals($expected, $sut->filter($value));
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public function dpFilterProvider()
    {
        return [
            ['icZs', '1CZS'],
            ['ic Z s', '1CZS'],
            ['icZsab cd efgh ijk lmno p qrs t u ', 'ICZSABCDEFGHIJK']
        ];
    }
}
