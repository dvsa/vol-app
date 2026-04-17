<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\UpdateConditionUndertaking as CommandHandler;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateConditionUndertaking as Command;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Mockery as m;

/**
 * UpdateConditionUndertakingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateConditionUndertakingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'TYPE',
            'ATTACHED_TO',
            'cu_cat_other',
        ];

        $this->references = [
            \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class => [
                32 => m::mock(\Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class),
            ],
            ApplicationEntity::class => [
                64 => m::mock(ApplicationEntity::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandFailValidation(): void
    {
        $data = [
            'attachedTo' => 'cat_oc',
        ];
        $command = Command::create($data);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoDelta(): void
    {
        $data = [
            'conditionUndertaking' => 154,
            'version' => 43,
            'type' => 'TYPE',
            'attachedTo' => 'ATTACHED_TO',
            'fulfilled' => 'Y',
            'notes' => 'FooBar',
            'conditionCategory' => 'cu_cat_other',
        ];
        $command = Command::create($data);

        $mockConditionUndertaking = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking->setId(154);
        $mockConditionUndertaking->setApplication(99);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 43)->once()
            ->andReturn($mockConditionUndertaking);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $cu) use ($data) {
                $this->assertSame($this->refData[$data['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$data['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($this->refData[$data['attachedTo']], $cu->getAttachedTo());
                $this->assertSame($data['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame($data['notes'], $cu->getNotes());
                $this->assertSame(null, $cu->getOperatingCentre());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['ConditionUndertaking updated'], $result->getMessages());
        $this->assertEquals(['conditionUndertaking' => 154], $result->getIds());
    }

    public function testHandleCommandNoDeltaWithOperatingCentre(): void
    {
        $data = [
            'conditionUndertaking' => 154,
            'version' => 43,
            'type' => 'TYPE',
            'attachedTo' => 'ATTACHED_TO',
            'fulfilled' => 'Y',
            'notes' => 'FooBar',
            'operatingCentre' => 32,
            'conditionCategory' => 'cu_cat_other',
        ];
        $command = Command::create($data);

        $mockConditionUndertaking = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking->setId(154);
        $mockConditionUndertaking->setApplication(99);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 43)->once()
            ->andReturn($mockConditionUndertaking);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $cu) use ($data) {
                $this->assertSame($this->refData[$data['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$data['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($this->refData[$data['attachedTo']], $cu->getAttachedTo());
                $this->assertSame($data['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame($data['notes'], $cu->getNotes());
                $this->assertSame(
                    $this->references[\Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre::class][32],
                    $cu->getOperatingCentre()
                );
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['ConditionUndertaking updated'], $result->getMessages());
        $this->assertEquals(['conditionUndertaking' => 154], $result->getIds());
    }

    public function testHandleCommandWithDelta(): void
    {
        $data = [
            'id' => 64,
            'conditionUndertaking' => 154,
            'version' => 43,
            'type' => 'TYPE',
            'attachedTo' => 'ATTACHED_TO',
            'fulfilled' => 'Y',
            'notes' => 'FooBar',
            'conditionCategory' => 'cu_cat_other',
        ];
        $command = Command::create($data);

        $mockConditionUndertaking = m::mock(\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking::class)->makePartial();
        $mockConditionUndertaking->setId(154);
        $mockConditionUndertaking->setOlbsKey(154);
        $mockConditionUndertaking->setOlbsType('foo');

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 43)->once()
            ->andReturn($mockConditionUndertaking);

        $this->repoMap['ConditionUndertaking']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $cu) use ($data, $mockConditionUndertaking) {
                $this->assertSame($this->refData[$data['type']], $cu->getConditionType());
                $this->assertSame($this->refData[$data['conditionCategory']], $cu->getConditionCategory());
                $this->assertSame($this->refData[$data['attachedTo']], $cu->getAttachedTo());
                $this->assertSame($data['fulfilled'], $cu->getIsFulfilled());
                $this->assertSame($data['notes'], $cu->getNotes());
                $this->assertSame(null, $cu->getOperatingCentre());
                $this->assertSame('U', $cu->getAction());
                $this->assertSame('Y', $cu->getIsDraft());
                $this->assertSame($mockConditionUndertaking, $cu->getLicConditionVariation());
                $this->assertSame(null, $cu->getLicence());
                $this->assertSame($this->references[ApplicationEntity::class][64], $cu->getApplication());
                $this->assertNull($cu->getOlbsKey());
                $this->assertNull($cu->getOlbsType());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['ConditionUndertaking updated'], $result->getMessages());
        $this->assertEquals(['conditionUndertaking' => 154], $result->getIds());
    }
}
