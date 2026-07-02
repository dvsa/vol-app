<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Helper;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\View\Helper\FormErrors;
use Common\Form\View\Helper\FormErrorsFactory;
use Laminas\I18n\Translator\TranslatorInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see FormErrorsFactory
 */
class FormErrorsFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockValidator = m::mock(FormElementMessageFormatter::class);
        $mockTranslator = m::mock(TranslatorInterface::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with(FormElementMessageFormatter::class)->andReturn($mockValidator);
        $container->expects('get')->with(TranslatorInterface::class)->andReturn($mockTranslator);

        $sut = new FormErrorsFactory();
        $this->assertInstanceOf(FormErrors::class, $sut->__invoke($container, FormErrors::class));
    }
}
