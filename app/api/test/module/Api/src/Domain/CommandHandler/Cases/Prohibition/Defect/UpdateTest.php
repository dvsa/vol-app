<?php

declare(strict_types=1);

/**
 * Update ProhibitionDefect Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect\Update as UpdateCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\ProhibitionDefect;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Prohibition\ProhibitionDefect as ProhibitionDefectEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Update as UpdateCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Prohibition\Defect Test
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var UpdateCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new UpdateCommandHandler();
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
        $id = 150;
        $version = 2;

        $data = [
            "id" => $id,
            "version" => $version,
            "prohibition" => "50",
            "defectType" => "My Defect Type String",
            "notes" => "My Defect Info String"
        ];

        $command = UpdateCommand::create($data);

        $this->repoMap['ProhibitionDefect']
            ->shouldReceive('fetchById')
            ->with($id, \Doctrine\Orm\Query::HYDRATE_OBJECT, $version)
            ->andReturn(
                m::mock(ProhibitionDefectEntity::class)

                    ->shouldReceive('setProhibition')
                    ->with(m::type(Entity\Prohibition\Prohibition::class))

                    // Get ID
                    ->shouldReceive('getId')
                    ->andReturn($id)

                    ->shouldReceive('setDefectType')
                    ->with("My Defect Type String")

                    ->shouldReceive('setNotes')
                    ->with("My Defect Info String")

                    ->getMock()
            )
            ->shouldReceive('save')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expectedResult = [
            'id' => [
                'defect' => $id,
            ],
            'messages' => [
                'Prohibition Defect Updated'
            ]
        ];

        $this->assertEquals($expectedResult, $result->toArray());
    }
}
