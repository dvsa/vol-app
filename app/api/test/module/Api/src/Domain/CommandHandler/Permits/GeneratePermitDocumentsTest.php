<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GenerateCoverLetterDocument;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\GeneratePermitDocument;
use Dvsa\Olcs\Api\Domain\Command\Permits\GeneratePermitDocuments as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\GeneratePermitDocuments as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * GeneratePermitDocumentsTest
 */
class GeneratePermitDocumentsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrhpPermit', IrhpPermitRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn(false);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('letters', 101)->addMessage('Cover letter #1 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('letters', 102)->addMessage('Cover letter #2 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('letters', 103)->addMessage('Cover letter #3 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
                'letters' => [101, 102, 103],
            ],
            'messages' => [
                'Cover letter #1 generated',
                'Permit #1 generated',
                'Cover letter #2 generated',
                'Permit #2 generated',
                'Cover letter #3 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForBilateral(): void
    {
        $countryId = CountryEntity::ID_NORWAY;

        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $licenceId1 = 10;
        $licenceId2 = 11;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isMultilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn(false);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType)
            ->shouldReceive('getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication1->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication2->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication3->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId2);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('letters', 101)->addMessage('Cover letter #1 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('letters', 103)->addMessage('Cover letter #3 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
                'letters' => [101, 103],
            ],
            'messages' => [
                'Cover letter #1 generated',
                'Permit #1 generated',
                'Permit #2 generated',
                'Cover letter #3 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommandForBilateralWithSuppressedCoverLetter(): array
    {
        return [
            [CountryEntity::ID_BELARUS],
            [CountryEntity::ID_GEORGIA],
            [CountryEntity::ID_KAZAKHSTAN],
            [CountryEntity::ID_MOROCCO],
            [CountryEntity::ID_RUSSIA],
            [CountryEntity::ID_TUNISIA],
            [CountryEntity::ID_TURKEY],
            [CountryEntity::ID_UKRAINE],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandForBilateralWithSuppressedCoverLetter')]
    public function testHandleCommandForBilateralWithSuppressedCoverLetter(mixed $countryId): void
    {
        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(true);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType)
            ->shouldReceive('getCountry->getId')
            ->withNoArgs()
            ->andReturn($countryId);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
            ],
            'messages' => [
                'Permit #1 generated',
                'Permit #2 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForMultilateral(): void
    {
        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $licenceId1 = 10;
        $licenceId2 = 11;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn(false);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication1->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication2->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication3->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId2);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('letters', 101)->addMessage('Cover letter #1 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('letters', 103)->addMessage('Cover letter #3 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
                'letters' => [101, 103],
            ],
            'messages' => [
                'Cover letter #1 generated',
                'Permit #1 generated',
                'Permit #2 generated',
                'Cover letter #3 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForEcmtShortTerm(): void
    {
        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $licenceId1 = 10;
        $licenceId2 = 11;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(true)
            ->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn(false);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication1->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication2->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication3->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId2);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('letters', 101)->addMessage('Cover letter #1 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('letters', 103)->addMessage('Cover letter #3 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
                'letters' => [101, 103],
            ],
            'messages' => [
                'Cover letter #1 generated',
                'Permit #1 generated',
                'Permit #2 generated',
                'Cover letter #3 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForEcmtRemoval(): void
    {
        $irhpPermitId1 = 1;
        $irhpPermitId2 = 2;
        $irhpPermitId3 = 3;

        $licenceId1 = 10;
        $licenceId2 = 11;

        $command = Cmd::Create(
            [
                'ids' => [
                    $irhpPermitId1, $irhpPermitId2, $irhpPermitId3
                ]
            ]
        );

        $irhpPermitType = m::mock(IrhpPermitTypeEntity::class);
        $irhpPermitType->shouldReceive('isBilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isMultilateral')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtShortTerm')
            ->withNoArgs()
            ->andReturn(false)
            ->shouldReceive('isEcmtRemoval')
            ->withNoArgs()
            ->andReturn(true);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication1->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication2->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId1);

        $irhpPermitApplication3 = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication3->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock->getIrhpPermitType')
            ->withNoArgs()
            ->andReturn($irhpPermitType);
        $irhpPermitApplication3->shouldReceive('getIrhpApplication->getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId2);

        $irhpPermit1 = m::mock(IrhpPermitEntity::class);
        $irhpPermit1->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication1);
        $irhpPermit1->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId1);

        $irhpPermit2 = m::mock(IrhpPermitEntity::class);
        $irhpPermit2->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication2);
        $irhpPermit2->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId2);

        $irhpPermit3 = m::mock(IrhpPermitEntity::class);
        $irhpPermit3->shouldReceive('getIrhpPermitApplication')->withNoArgs()->andReturn($irhpPermitApplication3);
        $irhpPermit3->shouldReceive('getId')->withNoArgs()->andReturn($irhpPermitId3);

        $this->repoMap['IrhpPermit']->shouldReceive('fetchById')
            ->with($irhpPermitId1, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit1)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId2, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit2)
            ->shouldReceive('fetchById')
            ->with($irhpPermitId3, Query::HYDRATE_OBJECT)
            ->andReturn($irhpPermit3);

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('letters', 101)->addMessage('Cover letter #1 generated')
        );

        $this->expectedSideEffect(
            GenerateCoverLetterDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('letters', 103)->addMessage('Cover letter #3 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId1,
            ],
            (new Result())->addId('permits', 201)->addMessage('Permit #1 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId2,
            ],
            (new Result())->addId('permits', 202)->addMessage('Permit #2 generated')
        );

        $this->expectedSideEffect(
            GeneratePermitDocument::class,
            [
                'irhpPermit' => $irhpPermitId3,
            ],
            (new Result())->addId('permits', 203)->addMessage('Permit #3 generated')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $expected = [
            'id' => [
                'permits' => [201, 202, 203],
                'letters' => [101, 103],
            ],
            'messages' => [
                'Cover letter #1 generated',
                'Permit #1 generated',
                'Permit #2 generated',
                'Cover letter #3 generated',
                'Permit #3 generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
