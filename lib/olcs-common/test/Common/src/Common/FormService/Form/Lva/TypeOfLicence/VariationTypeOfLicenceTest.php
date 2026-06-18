<?php

namespace Common\FormService\Form\Lva\TypeOfLicence;

use Common\Form\Elements\InputFilters\Lva\BackToVariationActionLink;
use Common\FormService\Form\Lva\Variation;
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
 * Variation Type Of Licence Test
 */
class VariationTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    /**
     * @var VariationTypeOfLicence
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

        $this->sut = new VariationTypeOfLicence($this->fh, $this->authService, $this->fsm);
    }

    public function testGetForm(): void
    {
        $params = [
            'canUpdateLicenceType' => true,
            'canBecomeSpecialRestricted' => true,
            'currentLicenceType' => 'foo',
            'currentVehicleType' => 'bar'
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element\Radio::class);

        $mockVt = m::mock(Element\Radio::class);

        $mockLtypSiContent = m::mock(Fieldset::class);
        $mockLtypSiContent->shouldReceive('get')
            ->with('vehicle-type')
            ->andReturn($mockVt);

        $ltFieldset = m::mock(Fieldset::class);
        $ltFieldset->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);
        $ltFieldset->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');
        $ltFieldset->shouldReceive('get')
            ->with('ltyp_siContent')
            ->andReturn($mockLtypSiContent);

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
            ->with($mockForm, 'type-of-licence->operator-type')
            ->shouldReceive('setCurrentOption')
            ->with($mockLt, 'foo')
            ->once()
            ->shouldReceive('setCurrentOption')
            ->with($mockVt, 'bar')
            ->once();

        $varService = m::mock(Variation::class);
        $this->fsm->setService('lva-variation', $varService);

        $varService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }

    public function testGetFormWithFalse(): void
    {
        $params = [
            'canUpdateLicenceType' => false,
            'canBecomeSpecialRestricted' => false,
            'currentLicenceType' => 'foo',
            'currentVehicleType' => 'bar'
        ];

        $mockOl = m::mock(Element::class);
        $mockOl->shouldReceive('setLabel')
            ->once()
            ->with('operator-location');

        $mockOt = m::mock(Element::class);
        $mockOt->shouldReceive('setLabel')
            ->once()
            ->with('operator-type');

        $mockLt = m::mock(Element\Radio::class);

        $mockVt = m::mock(Element\Radio::class);

        $mockLtypSiContent = m::mock(Fieldset::class);
        $mockLtypSiContent->shouldReceive('get')
            ->with('vehicle-type')
            ->andReturn($mockVt);

        $ltFieldset = m::mock(Fieldset::class);
        $ltFieldset->shouldReceive('get')
            ->with('licence-type')
            ->andReturn($mockLt);
        $ltFieldset->shouldReceive('setLabel')
            ->once()
            ->with('licence-type');
        $ltFieldset->shouldReceive('get')
            ->with('ltyp_siContent')
            ->andReturn($mockLtypSiContent);

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
        $formActions->shouldReceive('add')->once()->with(m::type(BackToVariationActionLink::class));

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
            ->with($mockLt, RefData::LICENCE_TYPE_SPECIAL_RESTRICTED)
            ->shouldReceive('setCurrentOption')
            ->with($mockLt, 'foo')
            ->once()
            ->shouldReceive('setCurrentOption')
            ->with($mockVt, 'bar')
            ->once();

        $varService = m::mock(Variation::class);
        $this->fsm->setService('lva-variation', $varService);

        $varService->shouldReceive('alterForm')
            ->once()
            ->with($mockForm);

        $form = $this->sut->getForm($params);

        $this->assertSame($mockForm, $form);
    }
}
