<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Form;
use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VariationBusinessDetails;

/**
 * Variation Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp(): void
    {
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->fh = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();
        $this->sut = new VariationBusinessDetails($this->fh, $this->fsm);
    }

    public function testAlterForm(): void
    {
        // Params
        $form = m::mock();
        $params = [
            'orgId' => 111,
            'orgType' => RefData::ORG_TYPE_LLP,
            'isLicenseApplicationPsv' => false
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);
        $mockLockBusinessDetailsFormService = m::mock(Form::class);

        $this->fsm->setService('lva-variation', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once()
            ->shouldReceive('remove')
            ->with($form, 'form-actions->cancel')
            ->once()
            ->getMock();

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }
}
