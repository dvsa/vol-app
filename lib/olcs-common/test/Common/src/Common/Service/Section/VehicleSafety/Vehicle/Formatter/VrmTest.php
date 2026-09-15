<?php

/**
 * Vrm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm;
use Mockery as m;

/**
 * Vrm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VrmTest extends \PHPUnit\Framework\TestCase
{
    /**
        protected function tearDown(): void
        {
        m::close();
        }

        /**
    */
    #[\PHPUnit\Framework\Attributes\Group('VrmFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield [
            ['id' => 2, 'vrm' => 'ABC123'],
            [],
            '<a class="govuk-link" href="{"child_id":2,"action":"edit"}">ABC123</a>'
        ];
        yield [
            ['id' => 2, 'vrm' => 'ABC123'],
            ['action-type' => 'large'],
            '<a class="govuk-link" href="{"child_id":2,"action":"large-edit"}">ABC123</a>'
        ];
        // The vrm is row data, so it is escaped wherever it lands in the anchor.
        yield 'hostile vrm' => [
            ['id' => 2, 'vrm' => '<script>alert(1)</script>'],
            [],
            '<a class="govuk-link" href="{"child_id":2,"action":"edit"}">'
            . '&lt;script&gt;alert(1)&lt;/script&gt;</a>'
        ];
    }
}
