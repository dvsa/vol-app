<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Form;
use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\FormService\Form\Lva\ApplicationBusinessDetails;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Business Details Form Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetailsTest extends MockeryTestCase
{
    use ButtonsAlterations;

    protected $sut;

    protected $sm;

    protected $fsm;

    protected $fh;

    public function setUp(): void
    {
        $this->fsm = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();
        $this->fh = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new ApplicationBusinessDetails($this->fh, $this->fsm);
    }

    public function testAlterFormWithoutInforceLicences(): void
    {
        // Params
        $form = m::mock();
        $this->mockAlterButtons($form, $this->fh);
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' => false,
            'isLicenseApplicationPsv' => false
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);

        $this->fsm->setService('lva-application', $mockApplicationFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $this->sut->alterForm($form, $params);
    }

    public function testAlterFormWithInforceLicences(): void
    {
        // Params
        $form = m::mock();
        $this->mockAlterButtons($form, $this->fh);
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' => true,
            'isLicenseApplicationPsv' => false
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);
        $mockLockBusinessDetailsFormService = m::mock(Form::class);

        $this->fsm->setService('lva-application', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }

    public function testAlterFormWithoutSubmittedLicenceApplication(): void
    {
        // Params
        $form = m::mock();
        $this->mockAlterButtons($form, $this->fh);
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' => false,
            'hasOrganisationSubmittedLicenceApplication' => false,
            'isLicenseApplicationPsv' => false
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);
        $mockLockBusinessDetailsFormService = m::mock(Form::class);

        $this->fsm->setService('lva-application', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->never()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }

    public function testAlterFormWithSubmittedLicenceApplication(): void
    {
        // Params
        $form = m::mock();
        $this->mockAlterButtons($form, $this->fh);
        $params = [
            'orgType' => RefData::ORG_TYPE_LLP,
            'hasInforceLicences' => false,
            'hasOrganisationSubmittedLicenceApplication' => true,
            'isLicenseApplicationPsv' => false
        ];

        // Mocks
        $mockApplicationFormService = m::mock(Form::class);
        $mockLockBusinessDetailsFormService = m::mock(Form::class);

        $this->fsm->setService('lva-application', $mockApplicationFormService);
        $this->fsm->setService('lva-lock-business_details', $mockLockBusinessDetailsFormService);

        // Expectations
        $mockApplicationFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->fh->shouldReceive('remove')
            ->with($form, 'allow-email')
            ->once();

        $mockLockBusinessDetailsFormService->shouldReceive('alterForm')
            ->once()
            ->with($form);

        $this->sut->alterForm($form, $params);
    }
}
