<?php

namespace CommonTest\Validation\Result;

use Common\Service\Validation\Result\ValidationFailed;
use Common\Service\Validation\CommandInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class ValidationFailedTest
 * @package CommonTest\Validation\Result
 */
class ValidationFailedTest extends TestCase
{
    public function testObject(): void
    {
        $command  = m::mock(CommandInterface::class);
        $sut = new ValidationFailed($command, ['failure' => 'It failed!']);

        $this->assertSame($command, $sut->getCommand());
        $this->assertEquals(['failure' => 'It failed!'], $sut->getMessages());
    }
}
