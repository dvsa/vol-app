<?php

/**
 * Variation TransportManager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\TransportManager;

use Common\Form\Elements\InputFilters\Lva\BackToVariationActionLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\TransportManager\VariationTransportManager as Sut;
use Zend\Form\Form;

/**
 * Variation TransportManager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationTransportManagerTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new Sut();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');

        $formActions->shouldReceive('has')->with('cancel')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('cancel');

        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('saveAndContinue');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToVariationActionLink::class));

        $form = m::mock();
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->sut->getForm();
    }

    public function testGetFormWithoutFormAction()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('remove')->once()->with('save');

        $formActions->shouldReceive('has')->with('cancel')->andReturn(false);
        $formActions->shouldReceive('remove')->never()->with('cancel');

        $formActions->shouldReceive('has')->with('saveAndContinue')->andReturn(false);
        $formActions->shouldReceive('remove')->never()->with('saveAndContinue');

        $formActions->shouldReceive('add')->once()->with(m::type(BackToVariationActionLink::class));

        $form = m::mock();
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\TransportManagers')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
