<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators\Messages;

use Common\Form\Elements\Validators\Messages\ValidatorDefaultMessageProvider;
use Laminas\Validator\ValidatorInterface;
use Laminas\Validator\ValidatorPluginManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see ValidatorDefaultMessageProvider
 */
class ValidatorDefaultMessageProviderTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $validatorName = 'validatorName';
        $messageKey = 'key';
        $messageValue = 'value';

        $messageTemplates = [$messageKey => $messageValue];

        $validator = m::mock(ValidatorInterface::class);
        $validator->expects('getMessageTemplates')->withNoArgs()->andReturn($messageTemplates);

        $pluginManager = m::mock(ValidatorPluginManager::class);
        $pluginManager->expects('get')->with($validatorName)->andReturn($validator);

        $sut = new ValidatorDefaultMessageProvider($pluginManager, $validatorName);
        $this->assertEquals($messageValue, $sut->__invoke($messageKey));
    }

    public function testInvokeMissingKey(): void
    {
        $validatorName = 'validatorName';

        $validator = m::mock(ValidatorInterface::class);
        $validator->expects('getMessageTemplates')->withNoArgs()->andReturn([]);

        $pluginManager = m::mock(ValidatorPluginManager::class);
        $pluginManager->expects('get')->with($validatorName)->andReturn($validator);

        $sut = new ValidatorDefaultMessageProvider($pluginManager, $validatorName);
        $this->assertNull($sut->__invoke('someKey'));
    }
}
