<?php

namespace Common\Test\Translator;

use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery\MockInterface;

trait MocksTranslatorsTrait
{
    abstract protected function serviceManager(): ServiceManager;

    protected function translator(): \Mockery\MockInterface
    {
        return $this->serviceManager()->get(TranslatorInterface::class);
    }

    protected function setUpDefaultTranslator(): \Mockery\MockInterface
    {
        $instance = $this->setUpMockService(TranslatorDelegator::class);
        $instance->shouldReceive('translate')->andReturnUsing(static fn($key) => $key)->byDefault();
        return $instance;
    }
}
