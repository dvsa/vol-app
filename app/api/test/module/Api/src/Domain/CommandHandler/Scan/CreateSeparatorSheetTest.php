<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Scan;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Scan\CreateSeparatorSheet as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Command\Scan\CreateSeparatorSheet as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Scan\CreateSeparatorSheet::class)]
class CreateSeparatorSheetTest extends AbstractCommandHandlerTestCase
{
    public const SUB_CAT_ID = 8001;
    public const SCAN_ID = 9999;

    /** @var  m\MockInterface | Category */
    private $mockCat;
    /** @var  m\MockInterface | SubCategory */
    private $mockSubCat;
    /** @var  m\MockInterface | Licence */
    private $mockLic;

    public function setUp(): void
    {
        $this->mockCat =  m::mock(Category::class);
        $this->mockSubCat = m::mock(SubCategory::class);

        $this->mockLic = m::mock(Licence::class)->makePartial();
        $this->mockLic->setId(32);
        $this->mockLic->setLicNo('LIC001');

        $this->sut = new CommandHandler();
        $this->mockRepo('Scan', \Dvsa\Olcs\Api\Domain\Repository\Scan::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('Bus', \Dvsa\Olcs\Api\Domain\Repository\Bus::class);
        $this->mockRepo('BusRegSearchView', \Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView::class);
        $this->mockRepo('Cases', \Dvsa\Olcs\Api\Domain\Repository\Cases::class);
        $this->mockRepo('Organisation', \Dvsa\Olcs\Api\Domain\Repository\Organisation::class);
        $this->mockRepo('TransportManager', \Dvsa\Olcs\Api\Domain\Repository\TransportManager::class);
        $this->mockRepo('IrhpApplication', \Dvsa\Olcs\Api\Domain\Repository\IrhpApplication::class);
        $this->mockRepo('Category', \Dvsa\Olcs\Api\Domain\Repository\Category::class);
        $this->mockRepo('SubCategory', \Dvsa\Olcs\Api\Domain\Repository\SubCategory::class);
        $this->mockRepo('SubCategoryDescription', \Dvsa\Olcs\Api\Domain\Repository\SubCategoryDescription::class);

        parent::setUp();

        $this->mockCat->setDescription('CAT_NAME');
        $this->mockSubCat->setSubCategoryName('SUB_CAT_NAME');

        $this->repoMap['Licence']
            ->shouldReceive('fetchByLicNo')->with('entityIdentifier')->andReturn($this->mockLic);
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->categoryReferences = [
            1 => $this->mockCat,
            2 => $this->mockCat,
            3 => $this->mockCat,
            4 => $this->mockCat,
            5 => $this->mockCat,
            7 => $this->mockCat,
            8 => $this->mockCat,
            9 => $this->mockCat,
        ];

        $this->subCategoryReferences = [
            self::SUB_CAT_ID => $this->mockSubCat,
        ];

        $this->references = [
            \Dvsa\Olcs\Api\Entity\Organisation\Organisation::class => [
                'entityIdentifier' => m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            ],
            \Dvsa\Olcs\Api\Entity\Tm\TransportManager::class => [
                'entityIdentifier' => m::mock(\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoDesc(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => 9,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => null,
            ]
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInvalidCategory(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => 666,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => 21,
                'description' => 'NOT USED',
            ]
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNoEntityForCategory(): void
    {
        $sut = m::mock(CommandHandler::class)->makePartial();
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $sut->getEntityTypeForCategory(-1);
    }

    public function testHandleCommandWithDescriptionId(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_APPLICATION,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => 44,
                'description' => 'NEVER',
            ]
        );

        $subCategoryDescription = new \Dvsa\Olcs\Api\Entity\System\SubCategoryDescription();
        $subCategoryDescription->setDescription('DESCRIPTION');

        $this->repoMap['SubCategoryDescription']->shouldReceive('fetchById')->with(44)->once()
            ->andReturn($subCategoryDescription);

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_APPLICATION], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('DESCRIPTION', $scan->getDescription());
                    $this->assertSame($this->mockLic, $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 654);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_APPLICATION,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_APPLICATION . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Licence',
                'ENTITY_ID_SCAN'             => 32,
                'ENTITY_ID_REPEAT_SCAN'      => 32,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'DESCRIPTION (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 654,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 654, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryApplication(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_APPLICATION,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_APPLICATION], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($this->mockLic, $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 987);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_APPLICATION,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_APPLICATION . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Licence',
                'ENTITY_ID_SCAN'             => 32,
                'ENTITY_ID_REPEAT_SCAN'      => 32,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 987,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 987, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryLicence(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_LICENSING,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_LICENSING], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($this->mockLic, $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 342);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_LICENSING,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_LICENSING . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Licence',
                'ENTITY_ID_SCAN'             => 32,
                'ENTITY_ID_REPEAT_SCAN'      => 32,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID .  ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 342,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 342, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryEnvironmental(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_ENVIRONMENTAL,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame(
                        $this->categoryReferences[Category::CATEGORY_ENVIRONMENTAL],
                        $scan->getCategory()
                    );
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($this->mockLic, $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 123);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_ENVIRONMENTAL,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_ENVIRONMENTAL . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Licence',
                'ENTITY_ID_SCAN'             => 32,
                'ENTITY_ID_REPEAT_SCAN'      => 32,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 123,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 123, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryCompliance(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_COMPLIANCE,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        /** @var \Dvsa\Olcs\Api\Entity\Cases\Cases $cases */
        $cases = m::mock(\Dvsa\Olcs\Api\Entity\Cases\Cases::class)->makePartial();
        $cases->setId(35);
        $cases->setLicence($this->mockLic);
        $cases->setTransportManager('TM');

        $this->repoMap['Cases']->shouldReceive('fetchById')->with('entityIdentifier')->once()
            ->andReturn($cases);

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) use ($cases) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_COMPLIANCE], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($cases, $scan->getCase());
                    $this->assertSame($cases->getLicence(), $scan->getLicence());
                    $this->assertSame($cases->getTransportManager(), $scan->getTransportManager());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 124);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_COMPLIANCE,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_COMPLIANCE . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Case',
                'ENTITY_ID_SCAN'             => 35,
                'ENTITY_ID_REPEAT_SCAN'      => 35,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 124,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 124, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryIrfo(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_IRFO,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_IRFO], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame(
                        $this->references[\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class]['entityIdentifier'],
                        $scan->getIrfoOrganisation()
                    );
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 125);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_IRFO,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_IRFO . ')',
                'LICENCE_NUMBER_SCAN'        => 'Unknown',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'Unknown',
                'ENTITY_ID_TYPE_SCAN'        => 'IRFO',
                'ENTITY_ID_SCAN'             => 'entityIdentifier',
                'ENTITY_ID_REPEAT_SCAN'      => 'entityIdentifier',
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 125,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 125, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryTransportManager(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_TRANSPORT_MANAGER,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame(
                        $this->categoryReferences[Category::CATEGORY_TRANSPORT_MANAGER],
                        $scan->getCategory()
                    );
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame(
                        $this->references[\Dvsa\Olcs\Api\Entity\Tm\TransportManager::class]['entityIdentifier'],
                        $scan->getTransportManager()
                    );
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 134);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_TRANSPORT_MANAGER,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_TRANSPORT_MANAGER . ')',
                'LICENCE_NUMBER_SCAN'        => 'Unknown',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'Unknown',
                'ENTITY_ID_TYPE_SCAN'        => 'Transport Manager',
                'ENTITY_ID_SCAN'             => 'entityIdentifier',
                'ENTITY_ID_REPEAT_SCAN'      => 'entityIdentifier',
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 134,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 134, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryBusReg(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_BUS_REGISTRATION,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'entityIdentifier',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $busRegSearchView = new \Dvsa\Olcs\Api\Entity\View\BusRegSearchView();
        $busRegSearchView->setId(783);

        $busReg = new \Dvsa\Olcs\Api\Entity\Bus\BusReg();
        $busReg->setLicence($this->mockLic);
        $busReg->setId(88);

        $this->repoMap['BusRegSearchView']->shouldReceive('fetchByRegNo')->with('entityIdentifier')->once()
            ->andReturn($busRegSearchView);
        $this->repoMap['Bus']->shouldReceive('fetchById')->with(783)->once()->andReturn($busReg);

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) use ($busReg) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame(
                        $this->categoryReferences[Category::CATEGORY_BUS_REGISTRATION],
                        $scan->getCategory()
                    );
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($busReg, $scan->getBusReg());
                    $this->assertSame($busReg->getLicence(), $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 123);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_BUS_REGISTRATION,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_BUS_REGISTRATION . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'Bus Route',
                'ENTITY_ID_SCAN'             => 88,
                'ENTITY_ID_REPEAT_SCAN'      => 88,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 123,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 123, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    public function testHandleCommandCategoryPermits(): void
    {
        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_PERMITS,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'OB1234567 / 100007',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        /** @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication */
        $irhpApplication = m::mock(\Dvsa\Olcs\Api\Entity\Permits\IrhpApplication::class)->makePartial();
        $irhpApplication->setId(35);
        $irhpApplication->setLicence($this->mockLic);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with('100007')
            ->andReturn($irhpApplication);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNo')
            ->with('OB1234567')
            ->andReturn($this->mockLic);

        $this->repoMap['Scan']->shouldReceive('save')->with(m::type(\Dvsa\Olcs\Api\Entity\PrintScan\Scan::class))
            ->once()->andReturnUsing(
                function (\Dvsa\Olcs\Api\Entity\PrintScan\Scan $scan) use ($irhpApplication) {
                    $scan->setId(self::SCAN_ID);
                    $this->assertSame($this->categoryReferences[Category::CATEGORY_PERMITS], $scan->getCategory());
                    $this->assertSame($this->subCategoryReferences[self::SUB_CAT_ID], $scan->getSubCategory());
                    $this->assertSame('TEST 1', $scan->getDescription());
                    $this->assertSame($irhpApplication, $scan->getIrhpApplication());
                    $this->assertSame($irhpApplication->getLicence(), $scan->getLicence());
                }
            );

        $result1 = new Result();
        $result1->addMessage('Create Document');
        $result1->addId('document', 124);
        $dtoData = [
            'template' => 'Scanning_SeparatorSheet',
            'query' => [],
            'knownValues' => [
                'DOC_CATEGORY_ID_SCAN'       => Category::CATEGORY_PERMITS,
                'DOC_CATEGORY_NAME_SCAN'     => 'CAT_NAME (' . Category::CATEGORY_PERMITS . ')',
                'LICENCE_NUMBER_SCAN'        => 'LIC001',
                'LICENCE_NUMBER_REPEAT_SCAN' => 'LIC001',
                'ENTITY_ID_TYPE_SCAN'        => 'IRHP Application',
                'ENTITY_ID_SCAN'             => 35,
                'ENTITY_ID_REPEAT_SCAN'      => 35,
                'DOC_SUBCATEGORY_ID_SCAN'    => self::SUB_CAT_ID,
                'DOC_SUBCATEGORY_NAME_SCAN'  => 'SUB_CAT_NAME (' . self::SUB_CAT_ID . ')',
                'DOC_DESCRIPTION_ID_SCAN'    => self::SCAN_ID,
                'DOC_DESCRIPTION_NAME_SCAN'  => 'TEST 1 (' . self::SCAN_ID . ')',
            ],
            'description' => 'Scanning separator',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_SCANNING_SEPARATOR,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $dtoData, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue::class,
            [
                'documentId' => 124,
                'jobName' => 'Scanning Separator Sheet'
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['document' => 124, 'scan' => self::SCAN_ID], $result->getIds());
        $this->assertSame(['Create Document', 'Scan ID ' . self::SCAN_ID . ' created'], $result->getMessages());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandCategoryPermitsBadIdentifierFormat')]
    public function testHandleCommandCategoryPermitsBadIdentifierFormat(mixed $entityIdentifier): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Identifier must contain a licence number, forward slash and IRHP application id'
        );

        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_PERMITS,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => $entityIdentifier,
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommandCategoryPermitsBadIdentifierFormat(): array
    {
        return [
            ['OB1234567 100007'],
            ['OB1234567 / 100007/'],
            ['OB1234567 . 100007'],
            ['OB1234567'],
        ];
    }

    public function testHandleCommandCategoryPermitsApplicationLicenceMismatch(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('IRHP application 35 does not belong to licence LIC001');

        $command = Cmd::create(
            [
                'categoryId' => Category::CATEGORY_PERMITS,
                'subCategoryId' => self::SUB_CAT_ID,
                'entityIdentifier' => 'LIC001 / 35',
                'descriptionId' => null,
                'description' => 'TEST 1',
            ]
        );

        $otherLicence = m::mock(Licence::class);
        $otherLicence->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(456);

        /** @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication */
        $irhpApplication = m::mock(\Dvsa\Olcs\Api\Entity\Permits\IrhpApplication::class)->makePartial();
        $irhpApplication->setId(35);
        $irhpApplication->setLicence($otherLicence);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with('35')
            ->andReturn($irhpApplication);

        $this->repoMap['Licence']->shouldReceive('fetchByLicNo')
            ->with('LIC001')
            ->andReturn($this->mockLic);

        $this->sut->handleCommand($command);
    }
}
