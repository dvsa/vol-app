<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\VehicleLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Vehicle Url formatter test
 */
class VehicleLinkTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new VehicleLink($this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     */
    public function testFormat(): void
    {

        $this->urlHelper
            ->shouldReceive('fromRoute')
            ->with(
                'licence/vehicle/view/GET',
                ['vehicle' => 1],
                [],
                true
            )
            ->andReturn('the_url');

        $this->assertEquals(
            '<a class="govuk-link" href="the_url">VRM</a>',
            $this->sut->format(
                [
                    'vehicle' =>
                        [
                            'id' => 1,
                            'vrm' => 'VRM'
                        ]
                ],
                []
            )
        );
    }
}
