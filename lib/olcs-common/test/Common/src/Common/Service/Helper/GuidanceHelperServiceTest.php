<?php

/**
 * Guidance Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Laminas\View\Helper\Placeholder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\GuidanceHelperService;

/**
 * Guidance Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GuidanceHelperServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var Placeholder */
    private $placeholder;

    #[\Override]
    protected function setUp(): void
    {
        $this->placeholder = m::mock(Placeholder::class);

        $this->sut = new GuidanceHelperService($this->placeholder);
    }

    public function testAppend(): void
    {
        $message = 'foo';

        // Expectations
        $this->placeholder->shouldReceive('getContainer')
            ->with('guidance')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('append')
                ->with($message)
                ->getMock()
            );

        $this->sut->append($message);
    }
}
