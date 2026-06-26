<?php

namespace CommonTest\Validation\Result;

use Common\Service\Validation\Result\ValidationSuccessful;
use Common\Service\Validation\CommandInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class PackValidationSuccessfulTest
 * @package CommonTest\Validation\Result
 */
class ValidationSuccessfulTest extends TestCase
{
    public function testObject(): void
    {
        $command  = m::mock(CommandInterface::class);
        $sut = new ValidationSuccessful($command, ['results' => 'Something valid'], ['some' => 'context']);

        $this->assertSame($command, $sut->getCommand());
        $this->assertEquals(['results' => 'Something valid'], $sut->getResult());
        $this->assertEquals(['some' => 'context'], $sut->getContext());
    }
}
