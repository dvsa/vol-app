<?php

/**
 * Licence Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceBusinessDetails;
use Common\Service\Entity\OrganisationEntityService;

/**
 * Licence Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    public function setUp()
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new LicenceBusinessDetails();
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testAlterForm()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => OrganisationEntityService::ORG_TYPE_LLP
        ];

        // Mocks
        $mockApplicationFormService = m::mock('\Common\FormService\FormServiceInterface');
        $mockLockBusinessDetailsFormService = m::mock('\Common\FormService\FormServiceInterface');

        $this->fsm->setService('lva-licence', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }
}
