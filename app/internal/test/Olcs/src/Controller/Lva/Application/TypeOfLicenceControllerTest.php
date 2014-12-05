<?php

/**
 * Type Of Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;

/**
 * Type Of Licence Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceControllerTest extends MockeryTestCase
{
    protected $sut;

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Lva\Application\TypeOfLicenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @group lva-controllers
     * @group lva-application-type-of-licence-controller
     */
    public function testGetSectionsForViewWithNoTypeOfLicenceData()
    {
        $id = 6;
        $stubbedTypeOfLicenceData = array(
            'licenceType' => '',
            'goodsOrPsv' => '',
            'niFlag' => ''
        );

        $stubbedSectionsForView = array(
            'overview' => array(
                'enabled' => true
            )
        );

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($id)
            ->shouldReceive('genericGetSectionsForView')
            ->andReturn($stubbedSectionsForView);

        $mockApplicationService = m::mock();
        $mockApplicationService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTypeOfLicenceData);

        $this->sm->setService('Entity\Application', $mockApplicationService);

        $sections = $this->sut->getSectionsForView();

        $this->assertFalse($sections['overview']['enabled']);
    }

    /**
     * @group lva-controllers
     * @group lva-application-type-of-licence-controller
     */
    public function testGetSectionsForView()
    {
        $id = 6;
        $stubbedTypeOfLicenceData = array(
            'licenceType' => 'foo',
            'goodsOrPsv' => 'bar',
            'niFlag' => 'baz'
        );

        $stubbedSectionsForView = array(
            'overview' => array(
                'enabled' => true
            )
        );

        $this->sut->shouldReceive('getApplicationId')
            ->andReturn($id)
            ->shouldReceive('genericGetSectionsForView')
            ->andReturn($stubbedSectionsForView);

        $mockApplicationService = m::mock();
        $mockApplicationService->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($stubbedTypeOfLicenceData);

        $this->sm->setService('Entity\Application', $mockApplicationService);

        $sections = $this->sut->getSectionsForView();

        $this->assertTrue($sections['overview']['enabled']);
    }
}
