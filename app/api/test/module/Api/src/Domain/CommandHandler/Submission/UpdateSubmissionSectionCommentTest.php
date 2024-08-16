<?php

/**
 * Update SubmissionSectionComment Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\UpdateSubmissionSectionComment;
use Dvsa\Olcs\Api\Domain\Repository\SubmissionSectionComment;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment as SubmissionSectionCommentEntity;
use Dvsa\Olcs\Transfer\Command\Submission\UpdateSubmissionSectionComment as Cmd;
use Dvsa\Olcs\Transfer\Command\Submission\DeleteSubmissionSectionComment;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update SubmissionSectionComment Test
 */
class UpdateSubmissionSectionCommentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateSubmissionSectionComment();
        $this->mockRepo('SubmissionSectionComment', SubmissionSectionComment::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            'case-summary'
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'submission' => 1,
            'submissionSection' => 'case-summary',
            'comment' => 'testing EDITED',
        ];

        $command = Cmd::create($data);

        /** @var SubmissionSectionCommentEntity $savedSubmissionSectionComment */
        $submissionSectionComment = m::mock(SubmissionSectionCommentEntity::class)->makePartial();
        $submissionSectionComment->setId(1);

        $submissionSection = $this->refData['case-summary'];
        $submissionSectionComment->shouldReceive('getSubmissionSection')->andReturn($submissionSection);

        $this->repoMap['SubmissionSectionComment']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($submissionSectionComment);

        /** @var SubmissionSectionCommentEntity $savedSubmissionSectionComment */
        $savedSubmissionSectionComment = null;

        $this->repoMap['SubmissionSectionComment']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionSectionCommentEntity::class))
            ->andReturnUsing(
                function (
                    SubmissionSectionCommentEntity $submissionSectionComment
                ) use (&$savedSubmissionSectionComment) {
                    $savedSubmissionSectionComment = $submissionSectionComment;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submissionSectionComment' => 1,
                'submissionSection' => 'case-summary'
            ],
            'messages' => [
                'Submission section comment updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals($data['comment'], $savedSubmissionSectionComment->getComment());
    }

    /**
     * Tests the comment is deleted if it's empty
     *
     * @param $comment
     *
     * @dataProvider emptyCommentProvider
     */
    public function testEmptyCommentDeleted($comment)
    {
        $commandData = [
            'id' => 1,
            'comment' => $comment,
        ];

        $command = Cmd::create($commandData);

        $this->expectedSideEffect(DeleteSubmissionSectionComment::class, ['id' => 1], new Result());

        $this->sut->handleCommand($command);
    }

    /**
     * @return array
     */
    public function emptyCommentProvider()
    {
        return [
            [null],
            ['']
        ];
    }
}
