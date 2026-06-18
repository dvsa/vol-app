<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators\Messages;

use Common\Form\Elements\Validators\Messages\FormElementMessageFormatter;
use Common\Form\Elements\Validators\Messages\FormElementMessageFormatterFactory;
use Common\Form\Elements\Validators\Messages\ValidatorDefaultMessageProvider;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @see FormElementMessageFormatterFactory
 */
class FormElementMessageFormatterFactoryTest extends MockeryTestCase
{
    protected const VALIDATOR_MANAGER = 'ValidatorManager';

    protected const MESSAGE_KEY = 'MESSAGE KEY';

    protected const VALIDATOR_NAME = 'VALIDATOR NAME';

    protected const CONFIG_SERVICE = 'config';

    protected const VALIDATION_CONFIG_NAMESPACE = 'validation';

    protected const DEFAULT_MESSAGE_TEMPLATES_TO_REPLACE_VARIABLE = 'default_message_templates_to_replace';

    protected FormElementMessageFormatterFactory $sut;

    public function testInvoke(): void
    {
        $config = [
            'validation' => [
                'default_message_templates_to_replace' => [
                    'MESSAGE KEY' => 'VALIDATOR NAME',
                ],
            ],
        ];

        $translator = m::mock(TranslatorInterface::class);

        $validatorPluginManager = m::mock(ValidatorPluginManager::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('has')->with('config')-> andReturnTrue();
        $container->expects('get')->with('config')-> andReturn($config);
        $container->expects('get')->with(TranslatorInterface::class)->andReturn($translator);
        $container->expects('get')->with('ValidatorManager')->andReturn($validatorPluginManager);

        $sut = new FormElementMessageFormatterFactory();
        $formatter = $sut->__invoke($container, FormElementMessageFormatter::class);
        $replacement = $formatter->getReplacementFor(static::MESSAGE_KEY);

        $this->assertInstanceOf(ValidatorDefaultMessageProvider::class, $replacement);
        $this->assertEquals(static::VALIDATOR_NAME, $replacement->getValidatorName());
    }
}
