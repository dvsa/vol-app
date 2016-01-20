<?php

/**
 * Variation Goods Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\Lva\BackToVariationActionLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\VariationGoodsVehicles;
use OlcsTest\Bootstrap;

/**
 * Variation Goods Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsVehiclesTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $formService;

    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->formHelper->shouldReceive('getServiceLocator')
            ->andReturn($this->sm);

        $this->formService = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new VariationGoodsVehicles();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->formService);
    }

    public function testGetForm()
    {
        // Params
        $mockTable = m::mock('\Common\Service\Table\TableBuilder');
        $isCrudPressed = true;

        // Mocks
        $mockForm = m::mock();
        $mockTableElement = m::mock('\Zend\Form\Fieldset');
        $mockValidator = m::mock();

        $this->sm->setService('oneRowInTablesRequired', $mockValidator);

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

        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');
        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');
        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToVariationActionLink::class));

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
