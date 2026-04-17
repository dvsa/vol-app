<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\SystemParameter;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\SystemParameter\Create as Create;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\SystemParameter\CreateSystemParameter as Cmd;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

/**
 * @see Create
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Create();
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);

        parent::setUp();
    }

    public function testHandleCommandNeedReassign(): void
    {
        $command = Cmd::create(['id' => 'foo', 'paramValue' => 'bar', 'description' => 'cake']);

        $this->repoMap['SystemParameter']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(SystemParameter::class))
            ->andReturnUsing(
                function (SystemParameter $sp) use (&$systemParameter) {
                    $sp->setId('foo');
                    $sp->setParamValue('bar');
                    $sp->setDescription('cake');
                    $systemParameter = $sp;
                }
            )
            ->getMock();

        $this->expectedListCacheClear(CacheEncryption::SYS_PARAM_LIST_IDENTIFIER);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['systemParameter' => 'foo'],
            'messages' => ["System Parameter 'foo' created"]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
