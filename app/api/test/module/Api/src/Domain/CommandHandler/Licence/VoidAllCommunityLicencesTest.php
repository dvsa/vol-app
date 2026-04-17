<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\VoidAllCommunityLicences as Command;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Licence\VoidAllCommunityLicences::class)]
class VoidAllCommunityLicencesTest extends AbstractCommandHandlerTestCase
{
    /** @var CommandHandler\Licence\VoidAllCommunityLicences */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\Licence\VoidAllCommunityLicences();

        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['id' => 717]);
        $this->repoMap['CommunityLic']
            ->shouldReceive('expireAllForLicence')
            ->with(717, CommunityLicEntity::STATUS_ANNUL)
            ->once();

        $this->expectedSideEffect(
            UpdateTotalCommunityLicencesCommand::class,
            ['id' => 717],
            new Result()
        );

        $this->sut->handleCommand($command);
    }
}
