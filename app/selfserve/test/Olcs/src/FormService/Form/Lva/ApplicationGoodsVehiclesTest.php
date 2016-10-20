<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\ApplicationGoodsVehicles;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;

/**
 * Application Goods Vehicles Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationGoodsVehiclesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationGoodsVehicles
     */
    protected $sut;

    protected $fh;

    protected $fsm;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->sut = new ApplicationGoodsVehicles();
        $this->sut->setFormHelper($this->fh);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testAlterForm()
    {
        $mockFieldset = m::mock(Fieldset::class);
        $mockTable = m::mock(TableBuilder::class);

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('table')
            ->andReturn($mockFieldset)
            ->once()
            ->getMock();

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\GoodsVehicles')
            ->andReturn($mockForm)
            ->shouldReceive('populateFormTable')
            ->with($mockFieldset, $mockTable)
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'shareInfo')
            ->once()
            ->getMock();

        $this->mockAlterButtons($mockForm, $this->fh);

        $this->fsm->shouldReceive('get')
            ->with('lva-application')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            )
            ->getMock();

        $form = $this->sut->getForm($mockTable);

        $this->assertSame($mockForm, $form);
    }
}

