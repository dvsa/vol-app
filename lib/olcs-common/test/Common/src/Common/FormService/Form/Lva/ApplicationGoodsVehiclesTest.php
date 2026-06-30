<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Lva\ApplicationGoodsVehicles;

class ApplicationGoodsVehiclesTest extends MockeryTestCase
{
    /**
     * @var \Mockery\LegacyMockInterface
     */
    public $authService;
    protected $sut;

    protected $formHelper;

    protected $formService;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class);
        $this->authService = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);
        $this->formService = m::mock(\Common\FormService\FormServiceManager::class)->makePartial();

        $this->sut = new ApplicationGoodsVehicles($this->formHelper, $this->authService, $this->formService);
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

        // <<--- START SUT::alterForm
        $mockApplication = m::mock('\Common\FormService\FormServiceInterface');
        $this->formService->setService('lva-application', $mockApplication);

        $mockApplication->shouldReceive('alterForm')
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
