<?php

/**
 * Vrm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm;
use Mockery as m;

/**
 * Vrm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VrmTest extends \PHPUnit\Framework\TestCase
{
    /**
    protected function tearDown(): void
    {
    m::close();
    }

    /**
     * @group VrmFormatter
     * @dataProvider provider
     */
    public function testFormat($data, $column, $expected): void
    {
        $mockUrlHelper = m::mock(UrlHelperService::class);
        $mockUrlHelper->shouldReceive('fromRoute')
            ->once()
            ->andReturnUsing(static fn($route, $params, $args, $routeMatch) => json_encode($params));

        $sut = new Vrm($mockUrlHelper);
        $output = $sut->format($data, $column);

        $this->assertEquals($expected, $output);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            [
                ['id' => 2, 'vrm' => 'ABC123'],
                [],
                '<a class="govuk-link" href="{"child_id":2,"action":"edit"}">ABC123</a>'
            ],
            [
                ['id' => 2, 'vrm' => 'ABC123'],
                ['action-type' => 'large'],
                '<a class="govuk-link" href="{"child_id":2,"action":"large-edit"}">ABC123</a>'
            ]
        ];
    }
}
