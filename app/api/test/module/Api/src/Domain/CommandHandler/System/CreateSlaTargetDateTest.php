<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\CreateSlaTargetDate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate as Repo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\SlaTargetDate as Entity;
use Dvsa\Olcs\Transfer\Command\System\CreateSlaTargetDate as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * CreateSlaTargetDate command handler test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class CreateSlaTargetDateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('SlaTargetDate', Repo::class);
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {

        $this->references = [
            Entity::class => [
                99 => m::mock(Entity::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'agreedDate' => '2015-09-10',
            'sentDate' => '2015-09-10',
            'underDelegation' => 'Y',
            'targetDate' => '2015-09-10',
            'notes' => 'test notes',
            'entityType' => 'document',
            'entityId' => 100
        ];
        $command = Command::create($params);

        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $mockUser->shouldReceive('getUser')
            ->andReturnSelf();

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        /** @var Entity $savedSlaTargetDate */
        $savedSlaTargetDate = null;

        $this->repoMap['SlaTargetDate']->shouldReceive('save')
            ->once()
            ->with(m::type(Entity::class))
            ->andReturnUsing(
                function (Entity $slaTargetDate) use (&$savedSlaTargetDate) {
                    $savedSlaTargetDate = $slaTargetDate;
                    $slaTargetDate->setId(55);
                }
            );

        $mockEntity = $this->getMockEntity($command->getEntityType(), $command->getEntityId());

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->once()
            ->andReturn($mockEntity);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['SlaTargetDate created successfully'], $result->getMessages());
    }

    public function testHandleCommandInvalidEntityType(): void
    {
        $params = [
            'agreedDate' => '2015-09-10',
            'sentDate' => '2015-09-10',
            'underDelegation' => 'Y',
            'targetDate' => '2015-09-10',
            'notes' => 'test notes',
            'entityType' => 'unsupported_type',
            'entityId' => 100
        ];
        $command = Command::create($params);
        $this->expectException(ValidationException::class);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInvalidEntityId(): void
    {
        $params = [
            'agreedDate' => '2015-09-10',
            'sentDate' => '2015-09-10',
            'underDelegation' => 'Y',
            'targetDate' => '2015-09-10',
            'notes' => 'test notes',
            'entityType' => 'document',
            'entityId' => 100
        ];
        $command = Command::create($params);

        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $mockUser->shouldReceive('getUser')
            ->andReturnSelf();

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->once()
            ->andReturnNull();

        $this->expectException(NotFoundException::class);

        $this->sut->handleCommand($command);
    }

    /**
     * Returns a mock entity based on entityType
     *
     * @param $entityType
     * @param $entityId
     * @return m\Mock|m\MockInterface
     */
    private function getMockEntity(mixed $entityType, mixed $entityId): mixed
    {
        $mock = m::mock();
        switch ($entityType) {
            case 'document':
                $mock = m::mock(Document::class)->makePartial();
                $mock->setId($entityId);
                break;
        }
        return $mock;
    }
}
