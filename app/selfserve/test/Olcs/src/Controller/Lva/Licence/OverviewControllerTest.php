<?php

/**
 * External Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;
use Olcs\View\Model\Licence\LicenceOverview as View;
use OlcsTest\Bootstrap;

/**
 * External Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexAction()
    {
        $licenceId = 69;

        $licenceData = [
            'id' => $licenceId,
            'licenceType' => [
                'id' => 'ltyp_sr',
            ],
            'sections' => [
                'foo',
                'bar',
            ],
            'licNo' => 'AB1234567',
            'expiryDate' => '2021-05-30',
            'inForceDate' => '2015-06-10',
            'status' => [
                'id' => 'lsts_valid',
            ],
        ];

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $this->expectQuery(LicenceQry::class, ['id' => $licenceId], $licenceData);

        $view = $this->sut->indexAction();
        $this->assertInstanceOf(View::class, $view);
    }

    public function testCreateVariationAction()
    {
        $licenceId = 3;
        $varId = 5;

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId)
            ->andReturn($varId);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => $varId])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariationAction());
    }
}
