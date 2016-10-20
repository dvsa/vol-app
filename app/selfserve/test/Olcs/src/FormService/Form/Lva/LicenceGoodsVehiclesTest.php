<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\LicenceGoodsVehicles;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;

/**
 * Licence Goods Vehicles Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceGoodsVehiclesTest extends MockeryTestCase
{
    /**
     * @var LicenceGoodsVehicles
     */
    protected $sut;

    protected $fh;

    protected $fsm;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->sut = new LicenceGoodsVehicles();
        $this->sut->setFormHelper($this->fh);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testAlterForm()
    {
        $mockFieldset = m::mock(Fieldset::class);
        $mockTable = m::mock(TableBuilder::class);

        $mockFormActions = m::mock()
            ->shouldReceive('has')
            ->with('cancel')
            ->andReturn(true)
            ->once()
            ->shouldReceive('remove')
            ->with('cancel')
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('table')
            ->andReturn($mockFieldset)
            ->once()
            ->shouldReceive('has')
            ->with('form-actions')
            ->andReturn(true)
            ->once()
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn($mockFormActions)
            ->once()
            ->getMock();

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\GoodsVehicles')
            ->andReturn($mockForm)
            ->shouldReceive('populateFormTable')
            ->with($mockFieldset, $mockTable)
            ->once()

            ->getMock();

        $this->fsm->shouldReceive('get')
            ->with('lva-licence')
            ->once()
            ->andReturn(
                m::mock()
                ->shouldReceive('alterForm')
                ->with($mockForm)
                ->once()
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('lva-licence-variation-vehicles')
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

