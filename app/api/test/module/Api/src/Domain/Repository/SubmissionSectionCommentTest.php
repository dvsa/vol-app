<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment::class)]
class SubmissionSectionCommentTest extends RepositoryTestCase
{
    public const SUBMISSION_ID = 8888;
    public const SUBMISSION_SECTION = 'submission_section';

    /** @var SubmissionSectionComment  */
    protected $sut;

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

        static::assertEquals($exists, $this->sut->isExist(Cmd::create($data)));

        //  check query
        $expect = 'QUERY ' .
            'AND m.submission = [[' . self::SUBMISSION_ID . ']] ' .
            'AND m.submissionSection = [[' . self::SUBMISSION_SECTION . ']] ' .
            'LIMIT 1';

        static::assertEquals($expect, $this->query);
    }

    public static function dpTestIsExistsProvider(): array
    {
        return [
            [['data'], true],
            [[], false],
            [null, false],
        ];
    }
}
