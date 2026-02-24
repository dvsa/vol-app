<?php

declare(strict_types=1);

/**
 * Schedule41ResetTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\ResetS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Schedule41Reset;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Approve as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class Schedule41ResetTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41ResetTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Schedule41Reset();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 510,
        ];

        $command = Cmd::create($data);

        $publication = m::mock(\Dvsa\Olcs\Api\Entity\Publication\Publication::class)->makePartial();
        $publication->setPubStatus(new RefData(\Dvsa\Olcs\Api\Entity\Publication\Publication::PUB_NEW_STATUS));
        $publicationLink1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink1
            ->setId(11)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: APP_NEW_SECTION))
            ->setPublication($publication);
        $publicationLink2 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink2
            ->setId(12)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: SCHEDULE_4_NEW))
            ->setPublication($publication);

        $application = m::mock(Application::class)
            ->shouldReceive('getId')->with()->andReturn(510)
            ->shouldReceive('getPublicationLinks')->with()->andReturn(
                new ArrayCollection([$publicationLink1, $publicationLink2])
            )
            ->shouldReceive('getS4s')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('matching')
                    ->andReturn(
                        [
                            m::mock(S4::class)
                                ->shouldReceive('getId')
                                ->once()
                                ->andReturn(1)
                                ->getMock()
                        ]
                    )
                    ->getMock()
            )->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')->with(510)->once()->andReturn($application);

        $this->expectedSideEffect(
            ResetS4::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 510],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\DeletePublicationLink::class,
            ['id' => 12],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandPublished(): void
    {
        $data = [
            'id' => 510,
        ];

        $command = Cmd::create($data);

        $publication = m::mock(\Dvsa\Olcs\Api\Entity\Publication\Publication::class)->makePartial();
        $publication->setPubStatus(new RefData(\Dvsa\Olcs\Api\Entity\Publication\Publication::PUB_PRINTED_STATUS));
        $publicationLink1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink1
            ->setId(11)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: APP_NEW_SECTION))
            ->setPublication($publication);
        $publicationLink2 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink2
            ->setId(12)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: SCHEDULE_4_NEW))
            ->setPublication($publication);

        $application = m::mock(Application::class)
            ->shouldReceive('getId')->with()->andReturn(510)
            ->shouldReceive('getPublicationLinks')->with()->andReturn(
                new ArrayCollection([$publicationLink1, $publicationLink2])
            )
            ->shouldReceive('getS4s')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('matching')
                    ->andReturn(
                        [
                            m::mock(S4::class)
                                ->shouldReceive('getId')
                                ->once()
                                ->andReturn(1)
                                ->getMock()
                        ]
                    )
                    ->getMock()
            )->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')->with(510)->once()->andReturn($application);

        $this->expectedSideEffect(
            ResetS4::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 510],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandGenerated(): void
    {
        $data = [
            'id' => 510,
        ];

        $command = Cmd::create($data);

        $publication = m::mock(\Dvsa\Olcs\Api\Entity\Publication\Publication::class)->makePartial();
        $publication->setPubStatus(new RefData(\Dvsa\Olcs\Api\Entity\Publication\Publication::PUB_GENERATED_STATUS));
        $publicationLink1 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink1
            ->setId(11)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: APP_NEW_SECTION))
            ->setPublication($publication);
        $publicationLink2 = new \Dvsa\Olcs\Api\Entity\Publication\PublicationLink();
        $publicationLink2
            ->setId(12)
            ->setPublicationSection((new PublicationSection())->setId(PublicationSection:: SCHEDULE_4_NEW))
            ->setPublication($publication);

        $application = m::mock(Application::class)
            ->shouldReceive('getId')->with()->andReturn(510)
            ->shouldReceive('getPublicationLinks')->with()->andReturn(
                new ArrayCollection([$publicationLink1, $publicationLink2])
            )
            ->shouldReceive('getS4s')
            ->once()
            ->andReturn(
                m::mock()->shouldReceive('matching')
                    ->andReturn(
                        [
                            m::mock(S4::class)
                                ->shouldReceive('getId')
                                ->once()
                                ->andReturn(1)
                                ->getMock()
                        ]
                    )
                    ->getMock()
            )->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')->with(510)->once()->andReturn($application);

        $this->expectedSideEffect(
            ResetS4::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 510],
            new Result()
        );

        $this->sut->handleCommand($command);
    }
}
