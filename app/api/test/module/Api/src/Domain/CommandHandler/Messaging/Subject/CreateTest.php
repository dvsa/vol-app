<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Messaging\Subject;

use Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Subject\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\Messaging\Subject\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo(Repo::class, Repo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            CategoryEntity::class => [
                1 => m::mock(CategoryEntity::class),
            ],
            SubCategoryEntity::class => [
                2 => m::mock(SubCategoryEntity::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([
            'description' => 'A new subject',
            'category' => 1,
            'subCategory' => 2,
            'isActive' => false,
        ]);

        $this->repoMap[Repo::class]
            ->shouldReceive('save')
            ->once()
            ->with(m::type(MessagingSubject::class))
            ->andReturnUsing(
                function (MessagingSubject $subject): void {
                    $this->assertSame('A new subject', $subject->getDescription());
                    $this->assertSame($this->references[CategoryEntity::class][1], $subject->getCategory());
                    $this->assertSame($this->references[SubCategoryEntity::class][2], $subject->getSubCategory());
                    $this->assertFalse($subject->getIsActive());
                    $subject->setId(7);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['messagingSubject' => 7], $result->getIds());
        $this->assertSame(['Messaging subject created'], $result->getMessages());
    }
}
