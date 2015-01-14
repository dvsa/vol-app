<?php

/**
 * Internal Licencing Operating Centres Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Internal Licencing Operating Centres Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\OperatingCentresController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    public function testAddAction()
    {
        $licenceId = 4;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $this->sut->shouldReceive('render')
            ->andReturnUsing(
                function ($in) {
                    return $in;
                }
            );

        $response = $this->sut->addAction();

        $this->assertInstanceof('\Zend\View\Model\ViewModel', $response);
        $this->assertEquals('licence/add-authorisation', $response->getTemplate());
        $this->assertEquals(['licence' => $licenceId], $response->getVariables());
    }
}
