<?php

namespace OlcsTest\FormService\Form\Lva\TypeOfLicence;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use Common\FormService\FormServiceManager;
use Common\Service\Table\TableBuilder;

/**
 * Application Type of Licence Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationTypeOfLicenceTest extends MockeryTestCase
{
    /**
     * @var ApplicationTypeOfLicence
     */
    protected $sut;

    protected $fh;

    protected $fsm;

    public function setUp()
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->fsm = m::mock(FormServiceManager::class)->makePartial();
        $this->sut = new ApplicationTypeOfLicence();
        $this->sut->setFormHelper($this->fh);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testAlterForm()
    {

        $mockForm = m::mock(Form::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('saveAndContinue')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setLabel')
                    ->with('lva.external.save_and_continue.button')
                    ->once()
                    ->getMock()
                )
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\TypeOfLicence')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->save')
            ->once()
            ->getMock();

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

        $form = $this->sut->getForm([]);

        $this->assertSame($mockForm, $form);
    }
}

