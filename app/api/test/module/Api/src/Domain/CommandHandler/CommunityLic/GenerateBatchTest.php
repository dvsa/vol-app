<?php

declare(strict_types=1);

/**
 * Generate Batch Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\GenerateBatch;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as Cmd;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCmd;
use Olcs\Logging\Log\Logger;

/**
 * Create Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GenerateBatchTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GenerateBatch();
        $this->mockRepo('CommunityLic', Repository\CommunityLic::class);
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceDataProvider')]
    public function testHandleCommand(
        mixed $ukLicenceDisabled,
        mixed $isBatchReprint,
        mixed $systemParam,
        mixed $isPsv,
        mixed $niFlag,
        mixed $template,
        mixed $subCategory,
        mixed $isLgv
    ): void {
        $licenceId = 1;
        $identifier = null;
        $communityLicenceIds = [10];
        $data = [
            'isBatchReprint' => $isBatchReprint,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds,
            'identifier' => $identifier
        ];

        $command = Cmd::create($data);

        $mockLicence = m::mock()
            ->shouldReceive('isPsv')
            ->andReturn($isPsv)
            ->twice()
            ->shouldReceive('isLgv')
            ->andReturn($isLgv)
            ->shouldReceive('getNiFlag')
            ->andReturn($niFlag)
            ->shouldReceive('getId')
            ->andReturn(1)
            ->getMock();

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->once()
            ->with($systemParam)
            ->andReturn($ukLicenceDisabled);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence);

        $docResult = new Result();
        $docResult->addId('document', 12);
        $docResult->addMessage('Create Document');
        $data = [
            'template' => $template,
            'query' => [
                'licence' => $licenceId,
                'communityLic' => $communityLicenceIds[0],
                'application' => $identifier
            ],
            'description' => 'Community licence',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => $subCategory,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $data, $docResult);

        $printResult = new Result();
        $printResult->addMessage('File printed');
        $printResult->addId('file', 1);
        $this->expectedSideEffect(
            EnqueueFileCmd::class,
            [
                'documentId' => 12,
                'jobName' => 'Community Licence'
            ],
            $printResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'file' => 1,
                'document' => 12
            ],
            'messages' => [
                'Create Document',
                'File printed',
                'Community Licence 10 processed'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceDataProvider')]
    public function testHandleCommandApplication(
        mixed $ukLicenceDisabled,
        mixed $isBatchReprint,
        mixed $systemParam,
        mixed $isPsv,
        mixed $niFlag,
        mixed $template,
        mixed $subCategory,
        mixed $isLgv
    ): void {
        $licenceId = 1;
        $identifier = 2;
        $communityLicenceIds = [10];
        $data = [
            'isBatchReprint' => $isBatchReprint,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds,
            'identifier' => $identifier
        ];

        $command = Cmd::create($data);

        $mockLicence = m::mock()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->getMock();

        $mockApplication = m::mock()
            ->shouldReceive('isPsv')
            ->andReturn($isPsv)
            ->twice()
            ->shouldReceive('isLgv')
            ->andReturn($isLgv)
            ->shouldReceive('getNiFlag')
            ->andReturn($niFlag)
            ->shouldReceive('getId')
            ->andReturn(2)
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->getMock();

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->once()
            ->with($systemParam)
            ->andReturn($ukLicenceDisabled);

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($identifier)
            ->andReturn($mockApplication);

        $docResult = new Result();
        $docResult->addId('document', 13);
        $docResult->addMessage('Create Document');
        $data = [
            'template' => $template,
            'query' => [
                'licence' => $licenceId,
                'communityLic' => $communityLicenceIds[0],
                'application' => $identifier
            ],
            'description' => 'Community licence',
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => $subCategory,
            'isExternal' => false,
            'isScan' => false
        ];
        $this->expectedSideEffect(GenerateAndStore::class, $data, $docResult);

        $printResult = new Result();
        $printResult->addMessage('File printed');
        $printResult->addId('file', 1);
        $this->expectedSideEffect(
            EnqueueFileCmd::class,
            [
                'documentId' => 13,
                'jobName' => 'Community Licence'
            ],
            $printResult
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'file' => 1,
                'document' => 13,
            ],
            'messages' => [
                'Create Document',
                'File printed',
                'Community Licence 10 processed'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public static function licenceDataProvider(): array
    {
        return [
            'reprint of PSV with new template switched off' => [
                1,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                true,
                'N',
                'PSV_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY,
                false
            ],
            'reprint of GV NI with new template switched off' => [
                1,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                false,
                'Y',
                'GV_NI_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'reprint of GV GB with new template switched off' => [
                1,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                false,
                'N',
                'GV_GB_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'new PSV with new template switched off' => [
                1,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                true,
                'N',
                'PSV_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY,
                false
            ],
            'new GV NI with new template switched off' => [
                1,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'Y',
                'GV_NI_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'new GV GB with new template switched off' => [
                1,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'N',
                'GV_GB_European_Community_Licence',
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'reprint PSV with new template switched on' => [
                0,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                true,
                'N',
                Document::GV_UK_COMMUNITY_LICENCE_PSV,
                SubCategory::DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY,
                false
            ],
            'reprint GV NI with new template switched on' => [
                0,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                false,
                'Y',
                Document::GV_UK_COMMUNITY_LICENCE_NI,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'reprint GV GB with new template switched on' => [
                0,
                true,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_REPRINT,
                false,
                'N',
                Document::GV_UK_COMMUNITY_LICENCE_GB,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'new PSV with new template switched on' => [
                0,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                true,
                'N',
                Document::GV_UK_COMMUNITY_LICENCE_PSV,
                SubCategory::DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY,
                false
            ],
            'new GV NI with new template switched on' => [
                0,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'Y',
                Document::GV_UK_COMMUNITY_LICENCE_NI,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'new GV GB with new template switched on' => [
                0,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'N',
                Document::GV_UK_COMMUNITY_LICENCE_GB,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                false
            ],
            'new LGV GB with new template switched on' => [
                0,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'N',
                Document::LGV_UK_COMMUNITY_LICENCE_GB,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                true
            ],
            'new LGV NI with new template switched on' => [
                0,
                false,
                SystemParameter::DISABLE_UK_COMMUNITY_LIC_OFFICE,
                false,
                'Y',
                Document::LGV_UK_COMMUNITY_LICENCE_NI,
                SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE,
                true
            ],
        ];
    }
}
