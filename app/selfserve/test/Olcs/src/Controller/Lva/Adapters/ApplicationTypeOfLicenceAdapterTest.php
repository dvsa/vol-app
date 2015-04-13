<?php

/**
 * External Application Type Of Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Lva\Adapters;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * External Application Type Of Licence Adapter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTypeOfLicenceAdapterTest extends MockeryTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = m::mock('Olcs\Controller\Lva\Adapters\ApplicationTypeOfLicenceAdapter')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testEmptyCreateTask()
    {
        $this->assertEquals(null, $this->sut->createTask(1, 1));
    }
}
