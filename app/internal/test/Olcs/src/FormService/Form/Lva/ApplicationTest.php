<?php

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Application;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    public function setUp(): void
    {
        $this->formHelper = m::mock('\Common\Service\Helper\FormHelperService');
        $this->sut = new Application($this->formHelper, m::mock(AuthorizationService::class));
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
