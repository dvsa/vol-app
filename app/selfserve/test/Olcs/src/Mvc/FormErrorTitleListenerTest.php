<?php

declare(strict_types=1);

namespace OlcsTest\Mvc;

use Laminas\Form\Form;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\FormErrorTitleListener;

final class FormErrorTitleListenerTest extends MockeryTestCase
{
    public function testSetsPlaceholderWhenNestedFormHasMessages(): void
    {
        $form = m::mock(Form::class);
        $form->shouldReceive('getMessages')->once()->andReturn(['field' => ['Invalid']]);
        $form->shouldNotReceive('isValid');

        $child = new ViewModel(['forms' => [$form]]);
        $view = new ViewModel();
        $view->addChild($child);

        $placeholder = new Placeholder();
        $event = new MvcEvent();
        $event->setResult($view);

        (new FormErrorTitleListener($placeholder))->onRender($event);

        $this->assertTrue($placeholder->getContainer('formHasErrors')->getValue());
    }

    public function testSetsPlaceholderForValidatedFormThatCannotSucceedWithoutMessages(): void
    {
        $form = m::mock(Form::class);
        $form->shouldReceive('getMessages')->once()->andReturn([]);
        $form->shouldReceive('hasValidated')->once()->andReturnTrue();
        $form->shouldReceive('isValid')->once()->andReturnFalse();

        $placeholder = new Placeholder();
        $event = new MvcEvent();
        $event->setResult(new ViewModel(['form' => $form]));

        (new FormErrorTitleListener($placeholder))->onRender($event);

        $this->assertTrue($placeholder->getContainer('formHasErrors')->getValue());
    }

    public function testDoesNotSetPlaceholderForUnvalidatedForm(): void
    {
        $form = m::mock(Form::class);
        $form->shouldReceive('getMessages')->once()->andReturn([]);
        $form->shouldReceive('hasValidated')->once()->andReturnFalse();
        $form->shouldNotReceive('isValid');

        $placeholder = new Placeholder();
        $event = new MvcEvent();
        $event->setResult(new ViewModel(['form' => $form]));

        (new FormErrorTitleListener($placeholder))->onRender($event);

        $this->assertSame([], $placeholder->getContainer('formHasErrors')->getValue());
    }

    public function testSetsPlaceholderForPageLevelErrorsWithoutFormErrors(): void
    {
        $placeholder = new Placeholder();
        $event = new MvcEvent();
        $event->setResult(new ViewModel(['pageHasErrors' => true]));

        (new FormErrorTitleListener($placeholder))->onRender($event);

        $this->assertTrue($placeholder->getContainer('formHasErrors')->getValue());
    }

    public function testDoesNotSetPlaceholderWhenPageHasNoErrors(): void
    {
        $placeholder = new Placeholder();
        $event = new MvcEvent();
        $event->setResult(new ViewModel(['pageHasErrors' => false]));

        (new FormErrorTitleListener($placeholder))->onRender($event);

        $this->assertSame([], $placeholder->getContainer('formHasErrors')->getValue());
    }
}
