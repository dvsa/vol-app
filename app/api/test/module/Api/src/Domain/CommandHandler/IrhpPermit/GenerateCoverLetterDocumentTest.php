<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GenerateCoverLetterDocument as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit\GenerateCoverLetterDocument as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * GenerateCoverLetterDocumentTest
 */
class GenerateCoverLetterDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $irhpPermitTypeId, mixed $expectedTemplate, mixed $expectedDescription, mixed $expectedMessages): void
    {
        $irhpPermitId = 1;
        $permitNo = 123;
        $licenceId = 10;
        $irhpAppId = 789;

        $command = Cmd::Create(
            [
                'irhpPermit' => $irhpPermitId
            ]
        );

        $licence = m::mock(Licence::class);
        $licence->expects('getId')->twice()->andReturn($licenceId);

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->expects('getLicence')->andReturn($licence);
        $irhpApplication->expects('getId')->andReturn($irhpAppId);

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->expects('getId')
            ->andReturn($irhpPermitTypeId);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->expects('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->andReturn($irhpPermitType);
        $irhpPermitApplication->expects('getIrhpApplication')->andReturn($irhpApplication);

        $irhpPermit = m::mock(IrhpPermitEntity::class);
        $irhpPermit->expects('getIrhpPermitApplication')->andReturn($irhpPermitApplication);
        $irhpPermit->expects('getId')->andReturn($irhpPermitId);
        $irhpPermit->expects('getPermitNumber')->andReturn($permitNo);

        $this->repoMap['IrhpPermit']->expects('fetchById')
            ->with($irhpPermitId, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => $expectedTemplate,
                'query' => [
                    'licence' => $licenceId,
                    'irhpPermit' => $irhpPermitId,
                ],
                'knownValues' => [],
                'irhpApplication' => $irhpAppId,
                'licence' => $licenceId,
                'description' => $expectedDescription,
                'category' => CategoryEntity::CATEGORY_PERMITS,
                'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_PERMIT_COVERING_LETTER,
                'isExternal' => false,
                'isScan' => false
            ],
            (new Result())->addId('document', 100)->addMessage('Document generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'coveringLetter' => 100,
            ],
            'messages' => $expectedMessages
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommand(): array
    {
        return [
            'ECMT Annual' => [
                'irhpPermitTypeId' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT,
                'expectedTemplate' => DocumentEntity::IRHP_PERMIT_ECMT_COVER_LETTER,
                'expectedDescription' => 'IRHP PERMIT ECMT COVERING LETTER 123',
                'expectedMessages' => [
                    'IRHP PERMIT ECMT COVERING LETTER 123 RTF created and stored',
                ],
            ],
            'ECMT Short-term' => [
                'irhpPermitTypeId' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'expectedTemplate' => DocumentEntity::IRHP_PERMIT_SHORT_TERM_ECMT_COVER_LETTER,
                'expectedDescription' => 'IRHP PERMIT SHORT TERM ECMT COVER LETTER 123',
                'expectedMessages' => [
                    'IRHP PERMIT SHORT TERM ECMT COVER LETTER 123 RTF created and stored',
                ],
            ],
            'ECMT Removal' => [
                'irhpPermitTypeId' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'expectedTemplate' => DocumentEntity::IRHP_PERMIT_ECMT_REMOVAL_COVERING_LETTER,
                'expectedDescription' => 'IRHP PERMIT ECMT REMOVALS COVERING LETTER 123',
                'expectedMessages' => [
                    'IRHP PERMIT ECMT REMOVALS COVERING LETTER 123 RTF created and stored',
                ],
            ],
            'IRHP Bilateral' => [
                'irhpPermitTypeId' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'expectedTemplate' => DocumentEntity::IRHP_PERMIT_ANN_BILAT_COVERING_LETTER,
                'expectedDescription' => 'IRHP PERMIT ANN BILAT COVERING LETTER 123',
                'expectedMessages' => [
                    'IRHP PERMIT ANN BILAT COVERING LETTER 123 RTF created and stored',
                ],
            ],
            'IRHP Multilateral' => [
                'irhpPermitTypeId' => IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                'expectedTemplate' => DocumentEntity::IRHP_PERMIT_ANN_MULTILAT_COVERING_LETTER,
                'expectedDescription' => 'IRHP PERMIT ANN MULTILAT COVERING LETTER 123',
                'expectedMessages' => [
                    'IRHP PERMIT ANN MULTILAT COVERING LETTER 123 RTF created and stored',
                ],
            ],
        ];
    }

    public function testHandleCommandForUndefinedTemplate(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Cover letter template not defined for IRHP Permit Type (id: undefined)');

        $irhpPermitTypeId = 'undefined';
        $irhpPermitId = 1;

        $command = Cmd::Create(
            [
                'irhpPermit' => $irhpPermitId
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('getId')
            ->andReturn($irhpPermitTypeId);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->andReturn($irhpPermitType);

        $irhpPermit = m::mock(IrhpPermitEntity::class);
        $irhpPermit->shouldReceive('getIrhpPermitApplication')->andReturn($irhpPermitApplication);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit);

        $this->sut->handleCommand($command);
    }
}
