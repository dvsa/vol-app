<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\DeleteListConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * DeleteListConditionUndertakingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteListConditionUndertakingTest extends AbstractCommandHandlerTestCase
{
    public const APP_ID = 65;
    public const CU_ID_1 = 9001;
    public const CU_ID_2 = 9002;

    public function setUp(): void
    {
        $this->sut = new DeleteListConditionUndertaking();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            \Dvsa\Olcs\Api\Entity\Application\Application::class => [
                self::APP_ID => m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => self::APP_ID,
            'ids' => [
                self::CU_ID_1,
                self::CU_ID_2,
            ],
        ];
        $command = TransferCmd\Variation\DeleteListConditionUndertaking::create($data);

        $mockConditionUndertaking1 = new \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking(
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1,
            1
        );
        $mockConditionUndertaking1
            ->setId(self::CU_ID_1)
            ->setApplication(32432);

        $mockConditionUndertaking2 = new \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking(
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            0,
            0
        );
        $mockConditionUndertaking2
            ->setId(self::CU_ID_2)
            ->setAttachedTo('ATTACHED_TO')
            ->setLicence('LICENCE');

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchById')->with(self::CU_ID_1)->once()->andReturn($mockConditionUndertaking1)
            ->shouldReceive('delete')->with($mockConditionUndertaking1)->once()
            ->shouldReceive('fetchById')->with(self::CU_ID_2)->once()->andReturn($mockConditionUndertaking2);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $cu) use ($mockConditionUndertaking2) {
                $this->assertSame('ATTACHED_TO', $cu->getAttachedTo());
                $this->assertSame(ConditionUndertaking::ACTION_DELETE, $cu->getAction());
                $this->assertSame($mockConditionUndertaking2, $cu->getLicConditionVariation());
                $this->assertSame(null, $cu->getLicence());
                $this->assertSame(
                    $this->references[\Dvsa\Olcs\Api\Entity\Application\Application::class][self::APP_ID],
                    $cu->getApplication()
                );
            }
        );

        $this->expectedSideEffect(
            DomainCmd\Application\UpdateApplicationCompletion::class,
            [
                'id' => self::APP_ID,
                'section' => 'conditionsUndertakings'
            ],
            new DomainCmd\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'ConditionUndertaking ID ' . self::CU_ID_1 . ' deleted',
                'ConditionUndertaking ID ' . self::CU_ID_2 . ' deleted',
            ],
            $result->getMessages()
        );
    }
}
