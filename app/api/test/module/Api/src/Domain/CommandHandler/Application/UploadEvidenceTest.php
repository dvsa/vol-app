<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UploadEvidence;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre as ApplicationOperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as ApplicationOperatingCentreRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UploadEvidence as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * UploadEvidenceTest
 */
class UploadEvidenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UploadEvidence();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentreRepo::class);
        $this->mockRepo('Task', TaskRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->categoryReferences = [
            CategoryEntity::CATEGORY_APPLICATION => m::mock(CategoryEntity::class),
        ];

        $this->subCategoryReferences = [
            SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL => m::mock(SubCategoryEntity::class),
            SubCategoryEntity::DOC_SUB_CATEGORY_ADVERT_DIGITAL => m::mock(SubCategoryEntity::class),
            SubCategoryEntity::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE => m::mock(SubCategoryEntity::class)
        ];

        $this->references = [
            OperatingCentreEntity::class => [
                222 => m::mock(OperatingCentreEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 111, 'financialEvidence' => true,  'supportingEvidence' => false]);

        $documentCollection = new ArrayCollection(
            [
                new DocumentEntity('doc1'),
                new DocumentEntity('doc2'),
            ]
        );

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setFinancialEvidenceUploaded(ApplicationEntity::FINANCIAL_EVIDENCE_SEND_IN_POST);
        $application->setId(10);
        $application->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )->once()->andReturn($documentCollection);
        $application->shouldReceive('getPostSubmissionApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )->once()->andReturn($documentCollection);
        $application->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(20)
                ->once()
                ->getMock()
            )
            ->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application)
            ->shouldReceive('save')->with($application)->once()->andReturn();

        $this->repoMap['Task']
            ->shouldReceive('fetchByAppIdAndDescription')
            ->with(10, TaskEntity::TASK_DESCRIPTION_FINANCIAL_EVIDENCE_UPLOADED)
            ->once()
            ->andReturn([]);

        $createTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL,
            'description' => TaskEntity::TASK_DESCRIPTION_FINANCIAL_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(TaskEntity::ACTION_DATE_FORMAT),
            'application' => 10,
            'licence' => 20
        ];
        $resultTask = new Result();
        $resultTask->addMessage('Task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $createTaskData, $resultTask);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Task created',
                'Financial evidence uploaded'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
        $this->assertSame(
            ApplicationEntity::FINANCIAL_EVIDENCE_UPLOADED,
            $application->getFinancialEvidenceUploaded()
        );
    }

    public function testHandleCommandFinancialEvidenceTaskExists(): void
    {
        $command = Cmd::create(['id' => 111, 'financialEvidence' => true, 'supportingEvidence' => false]);

        $documentCollection = new ArrayCollection(
            [
                new DocumentEntity('doc1'),
                new DocumentEntity('doc2'),
            ]
        );

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setFinancialEvidenceUploaded(ApplicationEntity::FINANCIAL_EVIDENCE_SEND_IN_POST);
        $application->setId(10);
        $application->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )->once()->andReturn($documentCollection);
        $application->shouldReceive('getPostSubmissionApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )->once()->andReturn($documentCollection);
        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application)
            ->shouldReceive('save')->with($application)->once()->andReturn();

        $this->repoMap['Task']
            ->shouldReceive('fetchByAppIdAndDescription')
            ->with(10, TaskEntity::TASK_DESCRIPTION_FINANCIAL_EVIDENCE_UPLOADED)
            ->once()
            ->andReturn(['task']);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Financial evidence uploaded'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
        $this->assertSame(
            ApplicationEntity::FINANCIAL_EVIDENCE_UPLOADED,
            $application->getFinancialEvidenceUploaded()
        );
    }

    public function testHandleCommandNoFinancialEvidenceDocs(): void
    {
        $command = Cmd::create(['id' => 111, 'financialEvidence' => true, 'supportingEvidence' => false]);

        $documentCollection = new ArrayCollection([]);

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setFinancialEvidenceUploaded(ApplicationEntity::FINANCIAL_EVIDENCE_SEND_IN_POST);
        $application->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )->once()->andReturn($documentCollection);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
        $this->assertSame(
            ApplicationEntity::FINANCIAL_EVIDENCE_SEND_IN_POST,
            $application->getFinancialEvidenceUploaded()
        );
    }

    public function testCommandHandlerWithOc(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'operatingCentres' => [
                    [
                        'aocId' => 3,
                        'adPlacedIn' => 'foo',
                        'adPlacedDate' => '2017-01-02'
                    ]
                ],
                'financialEvidence' => true
            ]
        );
        $mockOperatingCentre = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(222)
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('setAdPlaced')
            ->with(ApplicationOperatingCentreEntity::AD_UPLOAD_NOW)
            ->once()
            ->shouldReceive('setAdPlacedIn')
            ->with('foo')
            ->once()
            ->shouldReceive('setAdPlacedDate')
            ->with('2017-01-02')
            ->once()
            ->getMock();

        $documentCollection = new ArrayCollection([]);
        $documentCollectionNotEmpty = new ArrayCollection([new DocumentEntity('doc1')]);

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(10);
        $application
            ->shouldReceive('get')
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )
            ->andReturn($documentCollection)
            ->once()
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_ADVERT_DIGITAL],
                m::type(OperatingCentreEntity::class)
            )
            ->andReturn($documentCollectionNotEmpty)
            ->once()
            ->shouldReceive('getApplicationOperatingCentreById')
            ->with(3)
            ->andReturn($mockOperatingCentre)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(20)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application);

        $this->repoMap['Task']
            ->shouldReceive('fetchByAppIdAndDescription')
            ->with(10, TaskEntity::TASK_DESCRIPTION_OC_EVIDENCE_UPLOADED)
            ->once()
            ->andReturn([]);

        $createTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_ADVERT_DIGITAL,
            'description' => TaskEntity::TASK_DESCRIPTION_OC_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(TaskEntity::ACTION_DATE_FORMAT),
            'application' => 10,
            'licence' => 20
        ];
        $resultTask = new Result();
        $resultTask->addMessage('Task created');
        $this->expectedSideEffect(CreateTaskCmd::class, $createTaskData, $resultTask);

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('save')
            ->with($mockOperatingCentre)
            ->once();

        $expected = [
            'id' => [],
            'messages' => [
                'Task created',
                'Advert digital documents for OC 222 uploaded',
                'Advert details for OC 222 saved'
            ]
        ];
        $this->assertEquals($expected, $this->sut->handleCommand($command)->toArray());
    }

    public function testCommandHandlerWithOcTaskExists(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'operatingCentres' => [
                    [
                        'aocId' => 3,
                        'adPlacedIn' => 'foo',
                        'adPlacedDate' => '2017-01-02'
                    ]
                ],
                'financialEvidence' => true,
                'supportingEvidence' => false,
            ]
        );
        $mockOperatingCentre = m::mock()
            ->shouldReceive('getOperatingCentre')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(222)
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('setAdPlaced')
            ->with(ApplicationOperatingCentreEntity::AD_UPLOAD_NOW)
            ->once()
            ->shouldReceive('setAdPlacedIn')
            ->with('foo')
            ->once()
            ->shouldReceive('setAdPlacedDate')
            ->with('2017-01-02')
            ->once()
            ->getMock();

        $documentCollection = new ArrayCollection([]);
        $documentCollectionNotEmpty = new ArrayCollection([new DocumentEntity('doc1')]);

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(10);
        $application
            ->shouldReceive('get')
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )
            ->andReturn($documentCollection)
            ->once()
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_ADVERT_DIGITAL],
                m::type(OperatingCentreEntity::class)
            )
            ->andReturn($documentCollectionNotEmpty)
            ->once()
            ->shouldReceive('getApplicationOperatingCentreById')
            ->with(3)
            ->andReturn($mockOperatingCentre)
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application);

        $this->repoMap['Task']
            ->shouldReceive('fetchByAppIdAndDescription')
            ->with(10, TaskEntity::TASK_DESCRIPTION_OC_EVIDENCE_UPLOADED)
            ->once()
            ->andReturn(['task']);

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('save')
            ->with($mockOperatingCentre)
            ->once();

        $expected = [
            'id' => [],
            'messages' => [
                'Advert digital documents for OC 222 uploaded',
                'Advert details for OC 222 saved'
            ]
        ];
        $this->assertEquals($expected, $this->sut->handleCommand($command)->toArray());
    }

    public function testCommandHandlerNoOcException(): void
    {
        $this->expectException(ValidationException::class);

        $command = Cmd::create(
            [
                'id' => 111,
                'operatingCentres' => [
                    [
                        'aocId' => 3,
                        'adPlacedIn' => 'foo',
                        'adPlacedDate' => '2017-01-02'
                    ]
                ],
                'financialEvidence' => true
            ]
        );
        $documentCollection = new ArrayCollection([]);

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class);//->makePartial();
        $application
            ->shouldReceive('get')
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL]
            )
            ->andReturn($documentCollection)
            ->once()
            ->shouldReceive('getApplicationOperatingCentreById')
            ->with(3)
            ->andReturnNull()
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(111)->once()->andReturn($application);

        $this->sut->handleCommand($command)->toArray();
    }
    public function testHandleCommandWithSupportingDocs(): void
    {
        $command = Cmd::create(['id' => 10, 'financialEvidence' => false,'supportingEvidence' => true, ]);

        $documentCollection = new ArrayCollection([new DocumentEntity('doc1')]);

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)
            ->makePartial();

        $application->setId(10);
        $application
            ->shouldReceive('get')
            ->shouldReceive('getApplicationDocuments')
            ->with(
                $this->categoryReferences[CategoryEntity::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategoryEntity::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE]
            )->once()->andReturn($documentCollection);

        $application->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(20)
                    ->once()
                    ->getMock()
            )
            ->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')->with(10)->once()->andReturn($application)
            ->shouldReceive('save')->with($application)->once()->andReturn();

        $createTaskData = [
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE,
            'description' => TaskEntity::TASK_DESCRIPTION_SUPPORTING_EVIDENCE_UPLOADED,
            'actionDate' => (new DateTime('now'))->format(TaskEntity::ACTION_DATE_FORMAT),
            'application' => 10,
            'licence' => 20
            ];

        $resultTask = new Result();
        $this->expectedSideEffect(CreateTaskCmd::class, $createTaskData, $resultTask);

        $result = $this->sut->handleCommand($command);
        $expected = [
            'id' => [],
            'messages' => ['Supporting document uploaded']
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
