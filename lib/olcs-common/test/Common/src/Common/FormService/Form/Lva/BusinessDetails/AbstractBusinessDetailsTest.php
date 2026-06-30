<?php

namespace CommonTest\Common\FormService\Form\Lva\BusinessDetails;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\FormHelperService;

/**
 * Abstract Business Details Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AbstractBusinessDetailsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = m::mock(\Common\FormService\Form\Lva\BusinessDetails\AbstractBusinessDetails::class)->makePartial();
        $this->sut->__construct($this->formHelper);
    }

    public function testAlterFormRegisteredCompany(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_REGISTERED_COMPANY;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        // Expectations
        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm);

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testAlterFormLlp(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_LLP;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        // Expectations
        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm);

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testAlterFormSoleTrader(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_SOLE_TRADER;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        // Expectations
        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'table')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->companyNumber')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'registeredAddress')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->name')
            ->andReturnSelf();

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testAlterFormPartnership(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_PARTNERSHIP;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);
        $mockName = m::mock();

        // Expectations
        $mockForm->shouldReceive('get->get')
            ->with('name')
            ->andReturn($mockName);

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'table')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->companyNumber')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'registeredAddress')
            ->andReturnSelf()
            ->shouldReceive('alterElementLabel')
            ->with($mockName, '.partnership', FormHelperService::ALTER_LABEL_APPEND);

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testAlterFormOther(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_OTHER;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);
        $mockName = m::mock();

        // Expectations
        $mockForm->shouldReceive('get->get')
            ->with('name')
            ->andReturn($mockName);

        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'table')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->companyNumber')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'registeredAddress')
            ->andReturnSelf()
            ->shouldReceive('remove')
            ->with($mockForm, 'data->tradingNames')
            ->andReturnSelf()
            ->shouldReceive('alterElementLabel')
            ->with($mockName, '.other', FormHelperService::ALTER_LABEL_APPEND);

        $form = $this->sut->getForm($orgType, $hasInforceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testAlterFormRemovesSubsidiariesTableWhenPsvAndFormHasTable(): void
    {
        // Params
        $orgType = RefData::ORG_TYPE_LLP;
        $hasInforceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;
        $isLicenseApplicationPsv = true;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);

        // Expectations
        $this->formHelper->expects('createForm')
            ->with('Lva\BusinessDetails')
            ->andReturns($mockForm);

        $mockForm->expects('has')
            ->with('table')
            ->andReturns(true);

        $this->formHelper->expects('remove')
            ->with($mockForm, 'table')
            ->andReturnSelf();

        $form = $this->sut->getForm(
            $orgType,
            $hasInforceLicences,
            $hasOrganisationSubmittedLicenceApplication,
            $isLicenseApplicationPsv
        );

        // Assert
        $this->assertSame($mockForm, $form);
    }
}
