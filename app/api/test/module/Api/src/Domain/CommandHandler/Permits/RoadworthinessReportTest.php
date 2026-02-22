<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Dvsa\Olcs\Api\Domain\Command\Document\UploadCsv;
use Dvsa\Olcs\Api\Domain\Command\Permits\RoadworthinessReport as RoadworthinessReportCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\RoadworthinessReport as RoadworthinessReportHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPath;
use Dvsa\Olcs\Api\Entity\Note\Note;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Laminas\I18n\Translator\Translator;
use Mockery as m;

/**
 * @see RoadworthinessReportHandler
 */
class RoadworthinessReportTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RoadworthinessReportHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'translator' => m::mock(Translator::class),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $startDate = '2019-12-25';
        $endDate = '2019-12-31';
        $userId = 291;
        $uploadMsg1 = 'upload msg 1';
        $uploadMsg2 = 'upload msg 2';
        $uploadMsg3 = 'upload msg 3';

        $note1 = m::mock(Note::class);
        $note1->expects('getComment')->withNoArgs()->andReturn(' Note 1 ');

        $note2 = m::mock(Note::class);
        $note2->expects('getComment')->withNoArgs()->andReturn('Note 2');

        $document1IssuedBy = 'issued by name 1';
        $document1IssueDate = '2020-12-25';
        $document1 = $this->getDocument($document1IssuedBy, $document1IssueDate);

        $document2IssueDate = '2020-12-31';
        $document2 = $this->getDocumentWithMissingUser($document2IssueDate);

        $document3IssuedBy = 'issued by name 2';
        $document3IssueDate = '2021-02-14';
        $document3 = $this->getDocument($document3IssuedBy, $document3IssueDate);

        $document4IssueDate = '2021-04-01';
        $document4 = $this->getDocumentWithMissingUser($document4IssueDate);

        $this->addTranlatorAssertions(4, 2);

        $cmdData = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => $userId
        ];

        /**
         * applications 0 & 1 are trailers from the same app path
         * applications 2 & 3 are vehicles but from two different app paths
         * application 4 has no docs so is excluded
         */
        $irhpApplications = [
            0 => $this->getIrhpApplication(
                0,
                1,
                true,
                new ArrayCollection([$document1]),
                new ArrayCollection([$note1, $note2])
            ),
            1 => $this->getIrhpApplication(
                1,
                1,
                true,
                new ArrayCollection([$document2]),
                new ArrayCollection()
            ),
            2 => $this->getIrhpApplication(
                2,
                2,
                false,
                new ArrayCollection([$document3]),
                new ArrayCollection()
            ),
            3 => $this->getIrhpApplication(
                3,
                3,
                false,
                new ArrayCollection([$document4]),
                new ArrayCollection()
            ),
            4 => $this->getIrhpApplicationNoDocs(),
        ];

        //apps 0 and 1 have gone to the same trailer spreadsheet (they're from the same app path)
        $appPath1Rows = [
            0 => $this->getExpectedDataRow(
                0,
                true,
                $document1IssuedBy,
                $document1IssueDate,
                'Note 1 | Note 2'
            ),
            1 => $this->getExpectedDataRow(
                1,
                true,
                RoadworthinessReportHandler::MSG_USER_MISSING,
                $document2IssueDate,
                ''
            ),
        ];

        //app 2 goes to a vehicle spreadsheet for that app path
        $appPath2Rows = [
            0 => $this->getExpectedDataRow(
                2,
                false,
                $document3IssuedBy,
                $document3IssueDate,
                ''
            ),
        ];

        //app 3 goes to a vehicle spreadsheet for that app path
        $appPath3Rows = [
            0 => $this->getExpectedDataRow(
                3,
                false,
                RoadworthinessReportHandler::MSG_USER_MISSING,
                $document4IssueDate,
                ''
            ),
        ];

        $command = RoadworthinessReportCmd::create($cmdData);

        $this->repoMap['IrhpApplication']
            ->shouldReceive('fetchForRoadworthinessReport')
            ->with($startDate, $endDate)
            ->andReturn($irhpApplications);

        $appPath1UploadResult = new Result();
        $appPath1UploadResult->addMessage($uploadMsg1);

        $appPath2UploadResult = new Result();
        $appPath2UploadResult->addMessage($uploadMsg2);

        $appPath3UploadResult = new Result();
        $appPath3UploadResult->addMessage($uploadMsg3);

        $appPath1Data = $this->getUploadCsvCmdData(
            $appPath1Rows,
            'app path title 1',
            $userId
        );

        $appPath2Data = $this->getUploadCsvCmdData(
            $appPath2Rows,
            'app path title 2',
            $userId
        );

        $appPath3Data = $this->getUploadCsvCmdData(
            $appPath3Rows,
            'app path title 3',
            $userId
        );

        $this->expectedSideEffect(
            UploadCsv::class,
            $appPath1Data,
            $appPath1UploadResult
        );

        $this->expectedSideEffect(
            UploadCsv::class,
            $appPath2Data,
            $appPath2UploadResult
        );

        $this->expectedSideEffect(
            UploadCsv::class,
            $appPath3Data,
            $appPath3UploadResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                $uploadMsg1,
                $uploadMsg2,
                $uploadMsg3,
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Common set of assertions to bring back an IRHP application
     */
    private function getIrhpApplication(
        int $appNumber,
        int $appPathId,
        bool $isTrailer,
        ArrayCollection $documents,
        ArrayCollection $notes
    ): m\MockInterface {
        $qaData = [
            'custom-check-answers' => 'aaa',
            'custom-declaration' => 'bbb',
            'question 1' => [
                'questionShort' => $this->answerForAppNumber('question 1 translation key, app number', $appNumber),
                'answer' => $this->answerForAppNumber('answer 1, app number', $appNumber),
            ],
            'question 2' => [
                'questionShort' => $this->answerForAppNumber('question 2 translation key, app number', $appNumber),
                'answer' => $this->answerForAppNumber('answer 2, app number', $appNumber),
            ],
        ];

        $appPath = m::mock(ApplicationPath::class);
        $appPath->expects('getId')->withNoArgs()->andReturn($appPathId);
        $appPath->expects('getTitle')->withNoArgs()->andReturn('app path title ' . $appPathId);

        $operatorName = $this->answerForAppNumber('org name', $appNumber);
        $lastUpdateBy = $this->answerForAppNumber('last update by', $appNumber);
        $corCertNumber = $this->answerForAppNumber('cor cert number', $appNumber);
        $applicationRef = $this->answerForAppNumber('application ref', $appNumber);
        $applicationStatus = $this->answerForAppNumber('app status', $appNumber);
        $dateReceived = $this->answerForAppNumber('date received', $appNumber);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->expects('isCertificateOfRoadworthinessTrailer')
            ->withNoArgs()
            ->andReturn($isTrailer);
        $irhpApplication->expects('getRelatedOrganisation->getName')
            ->withNoArgs()
            ->andReturn($operatorName);
        $irhpApplication->expects('getLastModifiedBy->getContactDetails->getPerson->getFullName')
            ->withNoArgs()
            ->andReturn($lastUpdateBy);
        $irhpApplication->expects('getDocumentsByCategoryAndSubCategory')
            ->with(Category::CATEGORY_PERMITS, SubCategory::DOC_SUB_CATEGORY_PERMIT)
            ->andReturn($documents);
        $irhpApplication->expects('getActiveApplicationPath')
            ->withNoArgs()
            ->andReturn($appPath);
        $irhpApplication->expects('getNotes')->withNoArgs()->andReturn($notes);
        $irhpApplication->expects('getQuestionAnswerData')->withNoArgs()->andReturn($qaData);
        $irhpApplication->expects('getCorCertificateNumber')->withNoArgs()->andReturn($corCertNumber);
        $irhpApplication->expects('getApplicationRef')->withNoArgs()->andReturn($applicationRef);
        $irhpApplication->expects('getStatus->getDescription')->withNoArgs()->andReturn($applicationStatus);
        $irhpApplication->expects('getDateReceived')->withNoArgs()->andReturn($dateReceived);

        return $irhpApplication;
    }

    /**
     * Common set of assertions to bring back an IRHP application with no documents
     */
    private function getIrhpApplicationNoDocs(): m\MockInterface
    {
        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->expects('getDocumentsByCategoryAndSubCategory')
            ->with(Category::CATEGORY_PERMITS, SubCategory::DOC_SUB_CATEGORY_PERMIT)
            ->andReturn(new ArrayCollection());

        return $irhpApplication;
    }

    /**
     * common set of assertions to bring back a data row that corresponds to the app number
     */
    private function getExpectedDataRow(int $appNumber, bool $isTrailer, string $issuedBy, ?string $issueDate, string $notes): array
    {
        return [
            'Certificate no.' => $this->answerForAppNumber('cor cert number', $appNumber),
            'Operator name' => $this->answerForAppNumber('org name', $appNumber),
            'Application no.' => $this->answerForAppNumber('application ref', $appNumber),
            'Certificate type' => $isTrailer ? 'Trailer' : 'Vehicle',
            'Application status' => $this->answerForAppNumber('app status', $appNumber),
            'Date received' => $this->answerForAppNumber('date received', $appNumber),
            'Issue date' => $issueDate,
            'Issued by' => $issuedBy,
            'Last app update by' => $this->answerForAppNumber('last update by', $appNumber),
            $this->answerForAppNumber('question 1 translated, app number', $appNumber) =>
                $this->answerForAppNumber('answer 1, app number', $appNumber),
            $this->answerForAppNumber('question 2 translated, app number', $appNumber) =>
                $this->answerForAppNumber('answer 2, app number', $appNumber),
            'Notes' => $notes,
        ];
    }

    /**
     * provides a unique answer for an app number, allowing us to reuse the same base strings
     */
    private function answerForAppNumber(string $answer, int $appNumber): string
    {
        return $answer . ' ' . $appNumber;
    }

    /**
     * Reusable assertion for CSV upload
     */
    private function getUploadCsvCmdData(array $csvContent, string $fileDescription, int $userId): array
    {
        return [
            'csvContent' => $csvContent,
            'fileDescription' => $fileDescription,
            'category' => Category::CATEGORY_PERMITS,
            'subCategory' => SubCategory::REPORT_SUB_CATEGORY_PERMITS,
            'user' => $userId,
        ];
    }

    /**
     * Reusable assertions for documents to find the creator and issue date
     */
    private function getDocument(string $issuedBy, string $issueDate): m\MockInterface
    {
        $document = m::mock(Document::class);
        $document->expects('getCreatedBy->getContactDetails->getPerson->getFullName')
            ->withNoArgs()
            ->andReturn($issuedBy);
        $document->expects('getCreatedOn')
            ->with(true)
            ->andReturn(\DateTime::createFromFormat('Y-m-d', $issueDate));

        return $document;
    }

    /**
     * Reusable assertions for documents to find the creator and issue date
     */
    private function getDocumentWithMissingUser(string $issueDate): m\MockInterface
    {
        $document = m::mock(Document::class);
        $document->expects('getCreatedBy->getContactDetails->getPerson->getFullName')
            ->withNoArgs()
            ->andThrow(EntityNotFoundException::class);
        $document->expects('getCreatedOn')
            ->with(true)
            ->andReturn(\DateTime::createFromFormat('Y-m-d', $issueDate));

        return $document;
    }

    /**
     * The initial test code assumes there are 4 apps - 2 trailer and 2 vehicle, and that each app pulls
     * two questions from the Q&A system. This method prevents duplicate assertions and also allows the test
     * code to be more easily altered if extra apps or questions are needed
     */
    private function addTranlatorAssertions(mixed $numApps, mixed $numQuestions): void
    {
        for ($i = 0; $i < $numApps; $i++) {
            for ($j = 1; $j <= $numQuestions; $j++) {
                $this->addTranslatorAssertion($i, $j);
            }
        }
    }

    /**
     * Questions coming out of the Q&A system require translating, this is a reusable assertion for each question
     */
    private function addTranslatorAssertion(int $appNumber, int $questionNumber): void
    {
        $question = 'question ' . $questionNumber . ' translation key, app number';
        $translatedQuestion = 'question ' . $questionNumber . ' translated, app number';

        $this->mockedSmServices['translator']->expects('translate')
            ->with($this->answerForAppNumber($question, $appNumber))
            ->andReturn($this->answerForAppNumber($translatedQuestion, $appNumber));
    }
}
