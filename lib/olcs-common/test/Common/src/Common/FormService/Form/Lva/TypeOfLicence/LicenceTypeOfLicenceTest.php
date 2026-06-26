<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\Form\Elements\InputFilters\Lva\BackToLicenceActionLink;
use Common\FormService\Form\Lva\Licence;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Type Of Licence Test
 */
class LicenceTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    /**
     * @var LicenceTypeOfLicence
     */
    protected $sut;

    protected $fsm;

    protected $fh;

    #[\Override]
    protected function setUp(): void
    {
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->authService = m::mock(AuthorizationService::class);
        $this->sut = new LicenceTypeOfLicence($this->fh, $this->authService, $this->fsm);
    }

    public function testGetForm(): void
    {
        $params = [
            'canUpdateLicenceType' => true,
            'canBecomeSpecialRestricted' => true
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element::class);
        $mockLt->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');

        $tolFieldset = m::mock(Fieldset::class);
        $tolFieldset->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($mockOl)
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($mockOt)
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn($tolFieldset);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOl, 'operator-location-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOt, 'operator-type-lock-message')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-location')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-type');

        $licService = m::mock(Licence::class);
        $this->fsm->setService('lva-licence', $licService);

        $licService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithFalse(): void
    {
        $params = [
            'canUpdateLicenceType' => false,
            'canBecomeSpecialRestricted' => false
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element\Select::class);

        $ltFieldset = m::mock(Fieldset::class);
        $ltFieldset->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);
        $ltFieldset->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');

        $tolFieldset = m::mock(Fieldset::class);
        $tolFieldset->shouldReceive('get')
            ->with('operator-location')
            ->andReturn($mockOl)
            ->shouldReceive('get')
            ->with('operator-type')
            ->andReturn($mockOt)
            ->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($ltFieldset);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('type-of-licence')
            ->andReturn($tolFieldset);

        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');
        $formActions->shouldReceive('add')->once()->with(m::type(BackToLicenceActionLink::class));

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOl, 'operator-location-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($mockOt, 'operator-type-lock-message')
            ->shouldReceive('lockElement')
            ->once()
            ->with($ltFieldset, 'licence-type-lock-message')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-location')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->operator-type')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->licence-type->licence-type')
            ->shouldReceive('disableElement')
            ->once()
            ->with($mockForm, 'type-of-licence->licence-type->ltyp_siContent->vehicle-type')
            ->shouldReceive('disableElement')
            ->once()
            ->with(
                $mockForm,
                'type-of-licence->licence-type->ltyp_siContent->lgv-declaration->lgv-declaration-confirmation'
            )
            ->shouldReceive('removeOption')
            ->once()
            ->with($mockLt, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED);

        $licService = m::mock(Licence::class);
        $this->fsm->setService('lva-licence', $licService);

        $licService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }
}
