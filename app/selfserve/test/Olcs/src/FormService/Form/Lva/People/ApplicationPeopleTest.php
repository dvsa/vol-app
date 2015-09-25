<?php

/**
 * Application People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva\People;

use Common\Form\Elements\InputFilters\Lva\BackToApplicationActionLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\People\ApplicationPeople as Sut;
use Zend\Form\Form;

/**
 * Application People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationPeopleTest extends MockeryTestCase
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

        $formActions->shouldReceive('add')->once()->with(m::type(BackToApplicationActionLink::class));

        $form = m::mock();
        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->once()
            ->with('Lva\People')
            ->andReturn($form);

        $this->sut->getForm();
    }
}
