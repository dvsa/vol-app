<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\CommandHandler\SystemParameter\Update;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as Repo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as Entity;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\SystemParameter\UpdateSystemParameter as Cmd;
use Mockery as m;

/**
 * @see Update
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Update();
        $this->mockRepo('SystemParameter', Repo::class);
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 'NAME',
            'paramValue' => 'Foo',
            'description' => 'Bar'
        ];
        $command = Cmd::create($data);

        $systemParameter = new Entity();
        $this->repoMap['SystemParameter']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($systemParameter);

        $this->repoMap['SystemParameter']->shouldReceive('save')->with($systemParameter)->once();

        $this->expectedSingleCacheClear(CacheEncryption::SYS_PARAM_IDENTIFIER, 'NAME');
        $this->expectedListCacheClear(CacheEncryption::SYS_PARAM_LIST_IDENTIFIER);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'SystemParameter \'NAME\' updated'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());

        $this->assertSame('Foo', $systemParameter->getParamValue());
    }
}
