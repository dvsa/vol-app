<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormElementErrors;
use Common\Form\View\Helper\FormElementErrorsFactory;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @see FormElementErrorsFactory
 */
class FormElementErrorsFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $translator = m::mock(TranslatorInterface::class);
        $formElementMessageFormatter = m::mock(FormElementMessageFormatter::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(TranslatorInterface::class)->andReturn($translator);
        $container->expects('get')->with(FormElementMessageFormatter::class)->andReturn($formElementMessageFormatter);

        $sut = new FormElementErrorsFactory();
        $this->assertInstanceOf(FormElementErrors::class, $sut->__invoke($container, FormElementErrors::class));
    }
}
