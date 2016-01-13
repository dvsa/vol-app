<?php

/**
 * Application Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Application;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Application Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');

        $this->sut = new Application();
        $this->sut->setFormHelper($this->formHelper);
    }

    public function testAlterForm()
    {
        $form = m::mock(Form::class);
        $formActions = m::mock(Fieldset::class);

        $form->shouldReceive('has')->with('form-actions')->andReturn(true);
        $form->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $formActions->shouldReceive('has')->with('save')->andReturn(true);
        $formActions->shouldReceive('get->setLabel')->once()->with('internal.save.button');

        $this->sut->alterForm($form);
    }
}
