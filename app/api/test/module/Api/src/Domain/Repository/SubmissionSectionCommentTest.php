<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment as Entity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;

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
        $this->setUpRealSut(SubmissionSectionComment::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestIsExistsProvider')]
    public function testIsExist(mixed $queryResult, mixed $exists): void
    {
        $qb = $this->createRealQb()->willReturn($queryResult);

        $data = [
            'submission' => self::SUBMISSION_ID,
            'submissionSection' => self::SUBMISSION_SECTION,
        ];

        $this->assertSame($exists, $this->sut->isExist(Cmd::create($data)));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.submission = :SUBMISSION_ID AND m.submissionSection = :SUBMISSION_SECTION',
            $qb->getDQL(),
        );
        $this->assertSame(self::SUBMISSION_ID, $qb->getParameter('SUBMISSION_ID')->getValue());
        $this->assertSame(self::SUBMISSION_SECTION, $qb->getParameter('SUBMISSION_SECTION')->getValue());
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function dpTestIsExistsProvider(): \Iterator
    {
        yield [['data'], true];
        yield [[], false];
        yield [null, false];
    }
}
