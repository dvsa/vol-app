<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\Submission\StoreSubmissionSnapshot;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Submission\StoreSubmissionSnapshot as Cmd;
use Dvsa\Olcs\Api\Entity\Submission\Submission;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\Document\Upload;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

class StoreSubmissionSnapshotTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new StoreSubmissionSnapshot();
        $this->mockRepo('Submission', Repository\Submission::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(bool $hasLicence, string $licNo): void
    {
        $licence = $this->getLicence($hasLicence, $licNo);

        $caseId = 121;
        $submissionId = 15;

        $data = [
            'id' => $submissionId,
            'html' => 'HTML',
        ];
        $command = Cmd::create($data);

        /** @var Submission $savedSubmission */
        $submission = m::mock(Submission::class);
        $submission->expects('getId')->withNoArgs()->twice()->andReturn($submissionId);

        $case = m::mock(Cases::class);
        $case->expects('getId')->withNoArgs()->andReturn($caseId);
        $case->expects('getLicence')->withNoArgs()->andReturn($licence);

        $submission->shouldReceive('getSubmissionType->getDescription')->andReturn('DESC');
        $submission->expects('getCase')->andReturn($case);

        $this->repoMap['Submission']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($submission);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $params = [
            'content' => base64_encode(trim('HTML')),
            'case' => 121,
            'category' => Category::CATEGORY_SUBMISSION,
            'subCategory' => Category::SUBMISSION_SUB_CATEGORY_OTHER,
            'isExternal' => false,
            'isScan' => false,
            'filename' => 'DESC - Submission - ' . $submissionId . ' - Case ' . $caseId . ' - ' . $licNo . '.html',
            'description' => 'DESC - Submission - ' . $submissionId . ' - Case ' . $caseId . ' - ' . $licNo,
        ];
        $this->expectedSideEffect(Upload::class, $params, new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'submission' => $submissionId,
            ],
            'messages' => [
                'Submission snapshot created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function getLicence(bool $hasLicence, string $licNo): mixed
    {
        if (!$hasLicence) {
            return null;
        }

        $licence = m::mock(Licence::class);
        $licence->expects('getLicNo')->withNoArgs()->andReturn($licNo);

        return $licence;
    }

    public static function dpHandleCommand(): array
    {
        return [
            [true, 'AB123456'],
            [false, 'No attached licence'],
        ];
    }
}
