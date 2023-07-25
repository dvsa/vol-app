<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceBusinessDetails;
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

    public function setUp(): void
    {
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->formHelper = m::mock(FormHelperService::class)->makePartial();

        $this->sut = new LicenceBusinessDetails($this->formHelper, $this->fsm);
    }

    public function testAlterForm()
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => RefData::ORG_TYPE_LLP
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);
        $mockLockBusinessDetailsFormService = m::mock(Form::class);

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
