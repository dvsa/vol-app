<?php

declare(strict_types=1);

/**
 * Reopen Submission Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\ReopenSubmission;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;
use Dvsa\Olcs\Api\Entity\Submission\Submission as SubmissionEntity;
use Dvsa\Olcs\Transfer\Command\Submission\ReopenSubmission as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Reopen Submission Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ReopenSubmissionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReopenSubmission();
        $this->mockRepo('Submission', SubmissionRepo::class);

        parent::setUp();
    }

    public function testHandleCommandForClosedSubmission(): void
    {
        $command = Cmd::create(
            [
                'id' => 2
            ]
        );

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(2);
        $submission->setClosedDate(new \DateTime('now'));

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($submission);

        $this->repoMap['Submission']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionEntity::class))
            ->andReturnUsing(
                function (SubmissionEntity $submission) use (&$savedSubmission) {
                    $savedSubmission = $submission;
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Submission reopened', $result->getMessages());
        $this->assertNull($savedSubmission->getClosedDate());
    }

    /**
     * An open submission should not be re-openable, hence test for exception
     */
    public function testHandleCommandForOpenSubmission(): void
    {
        $command = Cmd::create(
            [
                'id' => 2
            ]
        );

        /** @var SubmissionEntity $savedSubmission */
        $submission = m::mock(SubmissionEntity::class)->makePartial();
        $submission->setId(2);

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($submission);

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }
}
