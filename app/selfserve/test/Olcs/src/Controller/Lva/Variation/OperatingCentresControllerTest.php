<?php

/**
 * External Variation Operating Centres Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * External Variation Operating Centres Controller Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OperatingCentresControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Variation\OperatingCentresController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test that postSave triggers fee handling via the adapter, and that it
     * still calls the section completion behavior
     */
    public function testPostSave()
    {
        $applicationId = 123;
        $this->sut->shouldReceive('getApplicationId')->andReturn($applicationId);

        $processing = m::mock()
            ->shouldReceive('setApplicationId')
                ->with($applicationId)
                ->andReturnSelf()
            ->shouldReceive('completeSection')
                ->once()
                ->with('my_section')
            ->getMock();

        $adapter = m::mock()->shouldReceive('handleFees')->once()->getMock();

        $this->sm->setService('Processing\VariationSection', $processing);
        $this->sut->shouldReceive('getAdapter')->andReturn($adapter);

        $this->sut->postSave('my_section');
    }
}
