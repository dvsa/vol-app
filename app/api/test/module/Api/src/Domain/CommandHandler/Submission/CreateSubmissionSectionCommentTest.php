<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmissionSectionComment;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionSectionComment as SubmissionSectionCommentEntity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionSectionComment as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmissionSectionComment::class)]
class CreateSubmissionSectionCommentTest extends AbstractCommandHandlerTestCase
{
    public const COMMENT_ID = 9999;

    /** @var CreateSubmissionSectionComment */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CreateSubmissionSectionComment();

        $this->mockRepo('SubmissionSectionComment', Repository\SubmissionSectionComment::class);

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class] = m::mock(\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
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

    public function testHandleCommandAlreadyExists(): void
    {
        $this->expectException(
            ValidationException::class
        );

        $cmd = Cmd::create([]);

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class]
            ->shouldReceive('convertJsonToHtml')
            ->andReturn('');

        $this->repoMap['SubmissionSectionComment']
            ->shouldReceive('isExist')->once()->with($cmd)->andReturn(true);

        $this->sut->handleCommand($cmd);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'submission' => 1,
            'submissionSection' => 'case-summary',
            'comment' => 'testing',
        ];

        $command = Cmd::create($data);

        $this->mockedSmServices[\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class]
            ->shouldReceive('convertJsonToHtml')
            ->with('testing')
            ->andReturn('testing');

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
