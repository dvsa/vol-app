<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment::class)]
final class SubmissionSectionCommentTest extends RepositoryTestCase
{
    public const int SUBMISSION_ID = 8888;
    public const string SUBMISSION_SECTION = 'submission_section';

    /** @var SubmissionSectionComment  */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(SubmissionSectionComment::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsExistsProvider')]
    public function testIsExist(mixed $queryResult, mixed $exists): void
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn($queryResult);

        $this->mockCreateQueryBuilder($qb);

        //  check result
        $data = [
            'submission' => self::SUBMISSION_ID,
            'submissionSection' => self::SUBMISSION_SECTION,
        ];

        $this->assertEquals($exists, $this->sut->isExist(Cmd::create($data)));

        //  check query
        $expect = 'QUERY ' .
            'AND m.submission = [[' . self::SUBMISSION_ID . ']] ' .
            'AND m.submissionSection = [[' . self::SUBMISSION_SECTION . ']] ' .
            'LIMIT 1';

        $this->assertEquals($expect, $this->query);
    }

    public static function dpTestIsExistsProvider(): \Iterator
    {
        yield [['data'], true];
        yield [[], false];
        yield [null, false];
    }
}
