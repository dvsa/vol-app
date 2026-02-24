<?php

declare(strict_types=1);

/**
 * Create Prohibition Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect\Create as CreateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as ProhibitionDefectEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Create as CreateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create ProhibitionDefect Test
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var CreateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateCommandHandler();
        $this->mockRepo('ProhibitionDefect', ProhibitionDefect::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'pro_t_si',
        ];

        $this->references = [
            Entity\Prohibition\Prohibition::class => [
                50 => m::mock(Entity\Prohibition\Prohibition::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 111;

        $data = [
            "prohibition" => "1",
            "defectType" => "My Defect Type String",
            "notes" => "My Defect Info String"
        ];

        $command = CreateCommand::create($data);

        /** @var $pro ProhibitionDefectEntity */
        $pro = null;

        $this->repoMap['ProhibitionDefect']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(ProhibitionDefectEntity::class))
            ->andReturnUsing(
                function (ProhibitionDefectEntity $entity) use (&$pro) {
                    $entity->setId(111);
                    $pro = $entity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'defect' => 111,
            ],
            'messages' => [
                'Prohibition Defect Created'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());

        $this->assertEquals(111, $pro->getId());
    }
}
