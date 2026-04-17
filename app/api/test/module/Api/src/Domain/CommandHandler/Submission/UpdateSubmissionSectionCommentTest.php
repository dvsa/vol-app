<?php

declare(strict_types=1);

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

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class] = m::mock(\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'case-summary'
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'submission' => 1,
            'submissionSection' => 'case-summary',
            'comment' => 'testing EDITED',
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class]
            ->shouldReceive('convertJsonToHtml')
            ->with('testing EDITED')
            ->andReturn('testing EDITED');

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
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('emptyCommentProvider')]
    public function testEmptyCommentDeleted(mixed $comment): void
    {
        $commandData = [
            'id' => 1,
            'comment' => $comment,
        ];

        $command = Cmd::create($commandData);

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class]
            ->shouldReceive('convertJsonToHtml')
            ->with($comment)
            ->andReturn($comment ?? '');

        $this->expectedSideEffect(DeleteSubmissionSectionComment::class, ['id' => 1], new Result());

        $this->sut->handleCommand($command);
    }

    /**
     * @return array
     */
    public static function emptyCommentProvider(): array
    {
        return [
            [null],
            ['']
        ];
    }
}
