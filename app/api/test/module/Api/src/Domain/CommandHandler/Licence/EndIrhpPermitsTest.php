<?php

declare(strict_types=1);

/**
 * EndIrhpPermitsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\EndIrhpPermits as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\Expire;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Terminate;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpPermits as Command;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Query\IrhpPermit\GetListByLicence;
use Mockery as m;

/**
 * EndIrhpPermitsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EndIrhpPermitsTest extends AbstractCommandHandlerTestCase
{
    public const LICENCE_ID = 52;

    private $licence;

    public function setUp(): void
    {
        $this->mockRepo('Licence', LicenceRepository::class);
        $this->mockRepo('IrhpPermit', IrhpPermitRepository::class);

        $this->licence = m::mock(Licence::class)->makePartial();

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LICENCE_ID)
            ->andReturn($this->licence);

        $this->sut = new CommandHandler();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithTaskCreation')]
    public function testHandleCommandWithTaskCreationFromActivePermitsAndValidApplications(
        mixed $context,
        mixed $expectedDescription
    ): void {
        $activeIrhpPermit1Id = 84;
        $activeIrhpPermit1 = m::mock(IrhpPermit::class);
        $activeIrhpPermit1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($activeIrhpPermit1Id);

        $activeIrhpPermit2Id = 86;
        $activeIrhpPermit2 = m::mock(IrhpPermit::class);
        $activeIrhpPermit2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($activeIrhpPermit2Id);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with(m::type(GetListByLicence::class), Query::HYDRATE_OBJECT)
            ->andReturnUsing(function ($query) use ($activeIrhpPermit1, $activeIrhpPermit2) {
                $this->assertEquals(self::LICENCE_ID, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return new ArrayCollection([$activeIrhpPermit1, $activeIrhpPermit2]);
            });

        $this->expectedSideEffect(
            Terminate::class,
            ['id' => $activeIrhpPermit1Id],
            new Result()
        );

        $this->expectedSideEffect(
            Terminate::class,
            ['id' => $activeIrhpPermit2Id],
            new Result()
        );

        $validIrhpApplication1Id = 123;
        $validIrhpApplication1 = m::mock(IrhpApplication::class);
        $validIrhpApplication1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($validIrhpApplication1Id);

        $validIrhpApplication2Id = 456;
        $validIrhpApplication2 = m::mock(IrhpApplication::class);
        $validIrhpApplication2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($validIrhpApplication2Id);

        $validIrhpApplications = new ArrayCollection([$validIrhpApplication1, $validIrhpApplication2]);

        $this->licence->shouldReceive('getValidIrhpApplications')
            ->withNoArgs()
            ->andReturn($validIrhpApplications);

        $this->expectedSideEffect(
            Expire::class,
            ['id' => $validIrhpApplication1Id],
            new Result()
        );

        $this->expectedSideEffect(
            Expire::class,
            ['id' => $validIrhpApplication2Id],
            new Result()
        );

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_PERMITS,
                'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
                'description' => $expectedDescription,
                'licence' => self::LICENCE_ID,
            ],
            new Result()
        );

        $command = Command::create(
            [
                'id' => self::LICENCE_ID,
                'context' => $context
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Cleared IRHP permits for licence 52'],
            $result->getMessages()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithTaskCreation')]
    public function testHandleCommandWithTaskCreationFromActivePermitsOnly(mixed $context, mixed $expectedDescription): void
    {
        $activeIrhpPermit1Id = 84;
        $activeIrhpPermit1 = m::mock(IrhpPermit::class);
        $activeIrhpPermit1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($activeIrhpPermit1Id);

        $activeIrhpPermit2Id = 86;
        $activeIrhpPermit2 = m::mock(IrhpPermit::class);
        $activeIrhpPermit2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($activeIrhpPermit2Id);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with(m::type(GetListByLicence::class), Query::HYDRATE_OBJECT)
            ->andReturnUsing(function ($query) use ($activeIrhpPermit1, $activeIrhpPermit2) {
                $this->assertEquals(self::LICENCE_ID, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return new ArrayCollection([$activeIrhpPermit1, $activeIrhpPermit2]);
            });

        $this->expectedSideEffect(
            Terminate::class,
            ['id' => $activeIrhpPermit1Id],
            new Result()
        );

        $this->expectedSideEffect(
            Terminate::class,
            ['id' => $activeIrhpPermit2Id],
            new Result()
        );

        $validIrhpApplications = new ArrayCollection([]);

        $this->licence->shouldReceive('getValidIrhpApplications')
            ->withNoArgs()
            ->andReturn($validIrhpApplications);

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_PERMITS,
                'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
                'description' => $expectedDescription,
                'licence' => self::LICENCE_ID,
            ],
            new Result()
        );

        $command = Command::create(
            [
                'id' => self::LICENCE_ID,
                'context' => $context
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Cleared IRHP permits for licence 52'],
            $result->getMessages()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandWithTaskCreation')]
    public function testHandleCommandWithTaskCreationFromValidApplicationsOnly(mixed $context, mixed $expectedDescription): void
    {
        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with(m::type(GetListByLicence::class), Query::HYDRATE_OBJECT)
            ->andReturnUsing(function ($query) {
                $this->assertEquals(self::LICENCE_ID, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return new ArrayCollection([]);
            });

        $validIrhpApplication1Id = 123;
        $validIrhpApplication1 = m::mock(IrhpApplication::class);
        $validIrhpApplication1->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($validIrhpApplication1Id);

        $validIrhpApplication2Id = 456;
        $validIrhpApplication2 = m::mock(IrhpApplication::class);
        $validIrhpApplication2->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($validIrhpApplication2Id);

        $validIrhpApplications = new ArrayCollection([$validIrhpApplication1, $validIrhpApplication2]);

        $this->licence->shouldReceive('getValidIrhpApplications')
            ->withNoArgs()
            ->andReturn($validIrhpApplications);

        $this->expectedSideEffect(
            Expire::class,
            ['id' => $validIrhpApplication1Id],
            new Result()
        );

        $this->expectedSideEffect(
            Expire::class,
            ['id' => $validIrhpApplication2Id],
            new Result()
        );

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_PERMITS,
                'subCategory' => Category::TASK_SUB_CATEGORY_PERMITS_GENERAL_TASK,
                'description' => $expectedDescription,
                'licence' => self::LICENCE_ID,
            ],
            new Result()
        );

        $command = Command::create(
            [
                'id' => self::LICENCE_ID,
                'context' => $context
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Cleared IRHP permits for licence 52'],
            $result->getMessages()
        );
    }

    public static function dpHandleCommandWithTaskCreation(): array
    {
        return [
            [
                EndIrhpApplicationsAndPermits::CONTEXT_SURRENDER,
                'Permits terminated after licence surrendered',
            ],
            [
                EndIrhpApplicationsAndPermits::CONTEXT_REVOKE,
                'Permits terminated after licence revoked',
            ],
            [
                EndIrhpApplicationsAndPermits::CONTEXT_CNS,
                'Permits terminated after CNS processing of licence',
            ],
        ];
    }

    public function testHandleCommandWithNoTaskCreation(): void
    {
        $this->repoMap['IrhpPermit']->shouldReceive('fetchList')
            ->with(m::type(GetListByLicence::class), Query::HYDRATE_OBJECT)
            ->andReturnUsing(function ($query) {
                $this->assertEquals(self::LICENCE_ID, $query->getLicence());
                $this->assertTrue($query->getValidOnly());

                return new ArrayCollection([]);
            });

        $validIrhpApplications = new ArrayCollection([]);

        $this->licence->shouldReceive('getValidIrhpApplications')
            ->withNoArgs()
            ->andReturn($validIrhpApplications);

        $command = Command::create(
            [
                'id' => self::LICENCE_ID,
                'context' => EndIrhpApplicationsAndPermits::CONTEXT_REVOKE,
            ]
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            ['Cleared IRHP permits for licence 52'],
            $result->getMessages()
        );
    }
}
