<?php

/**
 * Variation Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Elements\InputFilters\Lva\BackToVariationActionLink;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\PsvDiscs\VariationPsvDiscs;

/**
 * Variation Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationPsvDiscsTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    protected $fsm;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->fsm = m::mock('\Common\FormService\FormServiceManager')->makePartial();

        $this->sut = new VariationPsvDiscs();
        $this->sut->setFormHelper($this->formHelper);
        $this->sut->setFormServiceLocator($this->fsm);
    }

    public function testGetForm()
    {
        $formActions = m::mock();
        $formActions->shouldReceive('add')->once()->with(m::type(BackToVariationActionLink::class));

        // Mocks
        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper->shouldReceive('createForm')->with('Lva\PsvDiscs')->andReturn($mockForm);

        $form = $this->sut->getForm();

        $this->assertSame($mockForm, $form);
    }
}
