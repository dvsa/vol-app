<?php

/**
 * Create SubmissionAction Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\CreateSubmissionAction;
use Dvsa\Olcs\Api\Domain\Repository\SubmissionAction;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Submission\SubmissionAction as SubmissionActionEntity;
use Dvsa\Olcs\Transfer\Command\Submission\CreateSubmissionAction as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create SubmissionAction Test
 */
class CreateSubmissionActionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateSubmissionAction();
        $this->mockRepo('SubmissionAction', SubmissionAction::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            'sub_st_rec_grant_as'
        ];

        $this->references = [
            Submission::class => [
                11 => m::mock(Submission::class)
            ],
            Reason::class => [
                221 => m::mock(Reason::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'submission' => 11,
            'isDecision' => 'N',
            'actionTypes' => ['sub_st_rec_grant_as'],
            'reasons' => [221],
            'comment' => 'testing',
        ];

        $command = Cmd::create($data);

        /** @var SubmissionActionEntity $savedSubmissionAction */
        $savedSubmissionAction = null;

        $this->repoMap['SubmissionAction']->shouldReceive('save')
            ->once()
            ->with(m::type(SubmissionActionEntity::class))
            ->andReturnUsing(
                function (SubmissionActionEntity $submissionAction) use (&$savedSubmissionAction) {
                    $submissionAction->setId(111);
                    $savedSubmissionAction = $submissionAction;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submissionAction' => 111,
            ],
            'messages' => [
                'Submission Action created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[Submission::class][$data['submission']],
            $savedSubmissionAction->getSubmission()
        );
        $this->assertEquals($data['isDecision'], $savedSubmissionAction->getIsDecision());
        $this->assertSame(
            [$this->refData['sub_st_rec_grant_as']],
            $savedSubmissionAction->getActionTypes()
        );
        $this->assertEquals($data['comment'], $savedSubmissionAction->getComment());
        $this->assertSame(
            $this->references[Reason::class][$data['reasons'][0]],
            $savedSubmissionAction->getReasons()[0]
        );
    }

    public function testHandleInvalidCommand()
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'submission' => 11,
            'isDecision' => 'N',
            'actionTypes' => ['sub_st_rec_ptr'],
            'reasons' => [],
            'comment' => 'testing',
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
