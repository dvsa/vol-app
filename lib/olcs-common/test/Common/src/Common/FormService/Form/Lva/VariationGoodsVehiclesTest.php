<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\VariationGoodsVehicles;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Variation Goods Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsVehiclesTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    protected $sut;

    protected $formHelper;

    protected $formService;

    protected $sm;

    #[\Override]
    protected function setUp(): void
    {
        $this->authService = m::mock(AuthorizationService::class);
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->formHelper->shouldReceive('getServiceLocator')
            ->andReturn($this->sm);

        $this->formService = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new VariationGoodsVehicles($this->formHelper, $this->authService, $this->formService);
    }

    public function testGetForm(): void
    {
        // Params
        $mockTable = m::mock(\Common\Service\Table\TableBuilder::class);
        $isCrudPressed = true;

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);
        $mockTableElement = m::mock(\Laminas\Form\Fieldset::class);
        $mockValidator = m::mock();

        // Expectations
        $this->formHelper->shouldReceive('createForm')
            ->with('Lva\GoodsVehicles')
            ->andReturn($mockForm)
            ->shouldReceive('populateFormTable')
            ->with($mockTableElement, $mockTable)
            ->shouldReceive('remove')
            ->once()
            ->with($mockForm, 'shareInfo');

        $mockForm->shouldReceive('get')
            ->with('table')
            ->andReturn($mockTableElement);

        $mockForm->shouldReceive('getInputFilter->get->get->getValidatorChain->attach')
            ->with($mockValidator);

        $formActions = m::mock(\Laminas\Form\ElementInterface::class);
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        // <<--- START SUT::alterForm

        $mockLicenceVariationVehicles = m::mock('\Common\FormService\FormServiceInterface');
        $this->formService->setService('lva-licence-variation-vehicles', $mockLicenceVariationVehicles);

        $mockLicenceVariationVehicles->shouldReceive('alterForm')
            ->with($mockForm);
        // <<--- END SUT::alterForm

        $mockTableElement->shouldReceive('get->getValue')
            ->andReturn(10);

        $mockValidator->shouldReceive('setRows')
            ->with([10])
            ->shouldReceive('setCrud')
            ->with(true);

        $form = $this->sut->getForm($mockTable, $isCrudPressed);

        $this->assertSame($mockForm, $form);
    }
}
