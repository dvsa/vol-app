<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Task as Repo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Mockery as m;

final class TaskTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . TaskEntity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('singleFilterProvider')]
    public function testFetchByColumn(string $method, string $expectedWhere, string $parameter): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(1));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame(1, $qb->getParameter($parameter)->getValue());
    }

    public static function singleFilterProvider(): \Iterator
    {
        // The organisation parameter name carries a typo in the repository.
        yield 'by irfo organisation' => [
            'fetchByIrfoOrganisation',
            'm.irfoOrganisation = :organisaion',
            'organisaion',
        ];
        yield 'by transport manager' => [
            'fetchByTransportManager',
            'm.transportManager = :transportManager',
            'transportManager',
        ];
    }

    /**
     * isClosed is stored as a Y/N string here, unlike the integer flag the other methods use.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('openOnlyProvider')]
    public function testFetchByUser(bool $openOnly, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByUser(1, $openOnly));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.assignedToUser = :user' . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('user')->getValue());
    }

    public static function openOnlyProvider(): \Iterator
    {
        yield 'all tasks' => [false, ''];
        yield 'open only' => [true, ' AND m.isClosed = :isClosed'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tmCaseDecisionProvider')]
    public function testFetchForTmCaseDecision(string $subCategory, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->with(Query::HYDRATE_OBJECT)->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchForTmCaseDecision(1, 2, $subCategory));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.transportManager = :transportManager AND m.case = :case'
            . ' AND m.category = :category' . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(
            CategoryEntity::CATEGORY_TRANSPORT_MANAGER,
            $qb->getParameter('category')->getValue(),
        );
    }

    public static function tmCaseDecisionProvider(): \Iterator
    {
        yield 'no sub category' => ['', ''];
        yield 'with a sub category' => ['sub', ' AND m.subCategory = :subCategory'];
    }

    public function testFetchAssignedToSubmission(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->with(Query::HYDRATE_OBJECT)->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchAssignedToSubmission(1));

        // Here isClosed is compared against the integer literal 0, not the Y/N string.
        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.submission = :submission AND m.category = :category'
            . ' AND m.subCategory = :subCategory AND m.isClosed = 0',
            $qb->getDQL(),
        );
        $this->assertSame(CategoryEntity::CATEGORY_SUBMISSION, $qb->getParameter('category')->getValue());
        $this->assertSame(
            TaskEntity::SUBCATEGORY_SUBMISSION_ASSIGNMENT,
            $qb->getParameter('subCategory')->getValue(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isClosedProvider')]
    public function testFetchByAppIdAndDescription(bool $isClosed, string $expectedFlag): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByAppIdAndDescription(1, 'desc', $isClosed));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.application = :application AND m.description = :description'
            . ' AND m.isClosed = :isClosed',
            $qb->getDQL(),
        );
        $this->assertSame($expectedFlag, $qb->getParameter('isClosed')->getValue());
    }

    public static function isClosedProvider(): \Iterator
    {
        yield 'open' => [false, 'N'];
        yield 'closed' => [true, 'Y'];
    }

    public function testFetchOpenedTasksForLicences(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchOpenedTasksForLicences([1, 2], 'cat', 'sub', 'desc'),
        );

        // Task has no RefData associations, so withRefdata() adds nothing.
        $this->assertSame(
            'SELECT m, l' . self::FROM . ' LEFT JOIN m.licence l'
            . ' WHERE m.licence IN(:licenceIds) AND m.description = :description'
            . ' AND m.isClosed = :isClosed AND m.category = :categoryId'
            . ' AND m.subCategory = :subCategoryId',
            $qb->getDQL(),
        );
        $this->assertSame([1, 2], $qb->getParameter('licenceIds')->getValue());
        $this->assertSame(0, $qb->getParameter('isClosed')->getValue());
    }

    public function testFetchOpenTasksForSurrender(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenTasksForSurrender(1));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.surrender = :surrenderId AND m.isClosed = :isClosed',
            $qb->getDQL(),
        );
        $this->assertSame(0, $qb->getParameter('isClosed')->getValue());
    }

    public function testFlagUrgentsTasks(): void
    {
        $statement = m::mock();
        $statement->expects('fetchOne')->andReturn('5');

        $query = m::mock();
        $query->expects('execute')->andReturn($statement);

        $this->dbQueryService->expects('get')->with('Task\FlagUrgentTasks')->andReturn($query);

        $this->assertSame(5, $this->sut->flagUrgentsTasks());
    }

    /**
     * A team id wins outright; otherwise the user's own team is used; otherwise nothing.
     */
    public function testGetTeamReferenceByTeam(): void
    {
        $team = m::mock(Entity\User\Team::class);

        $this->em->expects('getReference')->with(Entity\User\Team::class, 999)->andReturn($team);

        $this->assertSame($team, $this->sut->getTeamReference(999, null));
    }

    public function testGetTeamReferenceByUser(): void
    {
        $user = m::mock(Entity\User\User::class);
        $user->expects('getTeam')->andReturn('EXPECT');

        $this->em->expects('getReference')->with(Entity\User\User::class, 666)->andReturn($user);

        $this->assertSame('EXPECT', $this->sut->getTeamReference(null, 666));
    }

    public function testGetTeamReferenceWithNeither(): void
    {
        $this->assertNull($this->sut->getTeamReference(null, null));
    }

    public function testGetTeamReferenceWhenTheUserCannotBeResolved(): void
    {
        $this->em->expects('getReference')->with(Entity\User\User::class, 6555)->andReturnNull();

        $this->assertNull($this->sut->getTeamReference(null, 6555));
    }
}
