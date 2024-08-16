<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmissionSectionComment;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment as SubmissionSectionCommentEntity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmissionSectionComment
 */
class CreateSubmissionSectionCommentTest extends AbstractCommandHandlerTestCase
{
    public const COMMENT_ID = 9999;

    /** @var CreateSubmissionSectionComment */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateSubmissionSectionComment();

        $this->mockRepo('SubmissionSectionComment', Repository\SubmissionSectionComment::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            'case-summary'
        ];

        $this->references = [
            Submission::class => [
                1 => m::mock(Submission::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandAlreadyExists()
    {
        $this->expectException(
            ValidationException::class
        );

        $cmd = Cmd::create([]);

        $this->repoMap['SubmissionSectionComment']
            ->shouldReceive('isExist')->once()->with($cmd)->andReturn(true);

        $this->sut->handleCommand($cmd);
    }

    public function testHandleCommand()
    {
        $data = [
            'submission' => 1,
            'submissionSection' => 'case-summary',
            'comment' => 'testing',
        ];

        $command = Cmd::create($data);

        /** @var SubmissionSectionCommentEntity $savedSubmissionSectionComment */
        $savedSubmissionSectionComment = null;

        $this->repoMap['SubmissionSectionComment']
            ->shouldReceive('isExist')->once()->with($command)->andReturn(false)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionSectionCommentEntity::class))
            ->andReturnUsing(
                function (
                    SubmissionSectionCommentEntity $submissionSectionComment
                ) use (&$savedSubmissionSectionComment) {
                    $submissionSectionComment->setId(self::COMMENT_ID);
                    $savedSubmissionSectionComment = $submissionSectionComment;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submissionSectionComment' => self::COMMENT_ID,
                'submissionSection' => 'case-summary'
            ],
            'messages' => [
                'Submission section comment created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[Submission::class][$data['submission']],
            $savedSubmissionSectionComment->getSubmission()
        );

        $this->assertEquals($data['comment'], $savedSubmissionSectionComment->getComment());
    }
}
