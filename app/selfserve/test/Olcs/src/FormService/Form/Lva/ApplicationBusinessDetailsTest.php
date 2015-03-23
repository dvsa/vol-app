<?php

/**
 * Application Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\FormService\Form\Lva\ApplicationBusinessDetails;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Application Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $fsm;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new ApplicationBusinessDetails();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testAlterFormWithoutInforceLicences()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => OrganisationEntityService::ORG_TYPE_LLP
        ];

        // Mocks
        $mockOrganisation = m::mock();
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->sm->setService('Entity\Organisation', $mockOrganisation);

        $this->fsm->setService('lva-application', $mockApplicationFormService);

        // Expectations
        $mockOrganisation->shouldReceive('hasInForceLicences')
            ->once()
            ->with(111)
            ->andReturn(false);

        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }

    public function testAlterFormWithInforceLicences()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => OrganisationEntityService::ORG_TYPE_LLP
        ];

        // Mocks
        $mockOrganisation = m::mock();
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');
        $mockLockBusinessDetailsFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->sm->setService('Entity\Organisation', $mockOrganisation);

        $this->fsm->setService('lva-application', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockOrganisation->shouldReceive('hasInForceLicences')
            ->once()
            ->with(111)
            ->andReturn(true);

        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }
}
