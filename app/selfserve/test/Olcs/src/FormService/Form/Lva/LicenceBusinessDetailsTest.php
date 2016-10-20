<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceBusinessDetails;
use Common\Service\Entity\OrganisationEntityService;
use Common\Service\Helper\FormHelperService;
use Common\FormService\FormServiceManager;

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
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->formHelper = m::mock(FormHelperService::class)->makePartial();

        $this->sut = new LicenceBusinessDetails();
        $this->sut->setFormServiceLocator($this->fsm);
        $this->sut->setFormHelper($this->formHelper);
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

        $this->formHelper->shouldReceive('remove')
            ->with($form, 'form-actions->cancel')
            ->once()
            ->getMock();

        $this->sut->alterForm($form, $params);
    }
}
