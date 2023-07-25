<?php

namespace OlcsTest\FormService\Form\Lva\BusinessType;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\BusinessType\ApplicationBusinessType;
use Laminas\Form\Form;
use Laminas\Form\Element;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use ZfcRbac\Service\AuthorizationService;

class ApplicationBusinessTypeTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationBusinessType
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    public function setUp(): void
    {
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->authService = m::mock(AuthorizationService::class);
        $this->guidanceHelper = m::mock('\Common\Service\Helper\GuidanceHelperService');

        $this->sut = new ApplicationBusinessType($this->fh, $this->authService, $this->guidanceHelper, $this->fsm);
    }

    public function testGetFormWithoutInforceLicencesOrWithNoSubmittedLicenceApplication()
    {
        $inForceLicences = false;
        $hasOrganisationSubmittedLicenceApplication = false;

        $mockForm = m::mock(Form::class);
        $this->mockAlterButtons($mockForm, $this->fh);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm);

        $mockApplication = m::mock(Form::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-application', $mockApplication);

        $form = $this->sut->getForm($inForceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithInforceLicences()
    {
        $inForceLicences = true;
        $hasOrganisationSubmittedLicenceApplication = false;

        $mockElement = m::mock(Element::class);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                ->shouldReceive('setLabel')
                ->with('lva.external.return.link')
                ->once()
                ->shouldReceive('removeAttribute')
                ->with('class')
                ->once()
                ->shouldReceive('setAttribute')
                ->with('class', 'govuk-button govuk-button--secondary')
                ->once()
                ->getMock()
            )
            ->times(3)
            ->getMock();

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->with('data')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('type')
                    ->andReturn($mockElement)
                    ->getMock()
            );

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type');

        $mockApplication = m::mock(Form::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-application', $mockApplication);

        $this->guidanceHelper
            ->shouldReceive('append')
            ->with('business-type.locked.message')
            ->once();

        $form = $this->sut->getForm($inForceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithSubmittedLicenceApplication()
    {
        $inForceLicences = false;
        $hasOrganisationSubmittedLicenceApplication = true;

        $mockElement = m::mock(Element::class);

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);

        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock()
                    ->shouldReceive('setLabel')
                    ->with('lva.external.return.link')
                    ->once()
                    ->shouldReceive('removeAttribute')
                    ->with('class')
                    ->once()
                    ->shouldReceive('setAttribute')
                    ->with('class', 'govuk-button govuk-button--secondary')
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->getMock();

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')->with('data')
            ->andReturn(
                m::mock()->shouldReceive('get')
                    ->with('type')
                    ->andReturn($mockElement)
                    ->getMock()
            );

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\BusinessType')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockElement, 'business-type.locked')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'data->type');

        $mockApplication = m::mock(Form::class);
        $mockApplication->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $this->fsm->setService('lva-application', $mockApplication);

        $this->guidanceHelper
                    ->shouldReceive('append')
                    ->with('business-type.locked.message')
                    ->once()
                    ->getMock();

        $form = $this->sut->getForm($inForceLicences, $hasOrganisationSubmittedLicenceApplication);

        $this->assertSame($mockForm, $form);
    }
}
