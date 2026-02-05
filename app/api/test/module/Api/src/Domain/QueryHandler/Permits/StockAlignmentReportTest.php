<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\StockAlignmentReport as StockAlignmentReportHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Permits\StockAlignmentReport as StockAlignmentReportQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class StockAlignmentReportTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new StockAlignmentReportHandler();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('IrhpPermitStock', IrhpPermitStockRepo::class);
        $this->mockRepo('Country', CountryRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $ranges, mixed $candidatePermits, mixed $expected): void
    {
        $stockId = 99;

        $stock = m::mock(IrhpPermitStock::class);
        $stock->shouldReceive('getIrhpPermitRanges')
            ->withNoArgs()
            ->once()
            ->andReturn($ranges);

        $this->repoMap['IrhpPermitStock']->shouldReceive('fetchById')
            ->with($stockId)
            ->once()
            ->andReturn($stock);

        $hungary = new Country();
        $hungary->setId(Country::ID_HUNGARY);
        $hungary->setCountryDesc('Hungary');

        $this->repoMap['Country']->shouldReceive('fetchById')
            ->with(Country::ID_HUNGARY)
            ->once()
            ->andReturn($hungary);

        $this->repoMap['IrhpApplication']->shouldReceive('getSuccessfulScoreOrderedInScope')
            ->with($stockId)
            ->once()
            ->andReturn($candidatePermits);

        $result = $this->sut->handleQuery(
            StockAlignmentReportQry::create(
                [
                    'id' => $stockId
                ]
            )
        );

        array_unshift($expected, ['Emissions category', 'Restricted countries', 'Number of permits']);

        $this->assertEquals(['rows' => $expected], $result);
    }

    public static function dpHandleQuery(): array
    {
        $euro5Desc = 'Euro 5';
        $euro5 = new RefData(RefData::EMISSIONS_CATEGORY_EURO5_REF);
        $euro5->setDescription($euro5Desc);

        $euro6Desc = 'Euro 6';
        $euro6 = new RefData(RefData::EMISSIONS_CATEGORY_EURO6_REF);
        $euro6->setDescription($euro6Desc);

        $austriaDesc = 'Austria';
        $austria = new Country();
        $austria->setId(Country::ID_AUSTRIA);
        $austria->setCountryDesc($austriaDesc);

        $greeceDesc = 'Greece';
        $greece = new Country();
        $greece->setId(Country::ID_GREECE);
        $greece->setCountryDesc($greeceDesc);

        $hungaryDesc = 'Hungary';
        $hungary = new Country();
        $hungary->setId(Country::ID_HUNGARY);
        $hungary->setCountryDesc($hungaryDesc);

        $italyDesc = 'Italy';
        $italy = new Country();
        $italy->setId(Country::ID_ITALY);
        $italy->setCountryDesc($italyDesc);

        $russiaDesc = 'Russian Federation';
        $russia = new Country();
        $russia->setId(Country::ID_RUSSIA);
        $russia->setCountryDesc($russiaDesc);

        // range - emissionsCategory, fromNo, toNo, countries
        // candidatePermit - emissionsCategory, countries
        // expected - emissionsCategory,countries,numberOfPermits
        return [
            'ignore SsReserve and LostReplacement ranges' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, true, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, true, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, StockAlignmentReportHandler::WITHOUT_RESTRICTED_COUNTRIES, 1],
                    [$euro6Desc, StockAlignmentReportHandler::WITHOUT_RESTRICTED_COUNTRIES, 1],
                ],
            ],
            'straight euro/country match' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, false, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 2],
                    [$euro6Desc, implode(',', [$austriaDesc, $greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 2],
                ],
            ],
            'split range based on requested countries' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, false, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$hungary]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$italy]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$hungary]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$italy]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc]), 1],
                    [$euro5Desc, implode(',', [$hungaryDesc]), 2],
                    [$euro5Desc, implode(',', [$hungaryDesc, $italyDesc]), 1],
                    [$euro5Desc, implode(',', [$hungaryDesc, $russiaDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$greeceDesc, $hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc, $italyDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc, $russiaDesc]), 1],
                ],
            ],
            'different order of restricted countries' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 1, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro5, null, 2, 2, false, false, new ArrayCollection([$greece, $italy, $russia, $hungary]), null, null),
                    IrhpPermitRange::create(null, $euro5, null, 3, 3, false, false, new ArrayCollection([$russia, $italy, $greece, $hungary]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 1, false, false, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 2, 2, false, false, new ArrayCollection([$greece, $italy, $russia, $austria, $hungary]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 3, 3, false, false, new ArrayCollection([$russia, $italy, $austria, $greece, $hungary]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece, $austria, $hungary, $russia, $italy]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$russia, $italy, $hungary, $greece, $austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$greece, $austria, $hungary, $russia, $italy]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$russia, $italy, $hungary, $greece, $austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 3],
                    [$euro6Desc, implode(',', [$austriaDesc, $greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 3],
                ],
            ],
            'use available euro5 permits for requested euro6' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, false, new ArrayCollection([$austria, $hungary]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $hungary]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc, $italyDesc, $russiaDesc]), 1],
                ],
            ],
            'running out of permits for restricted countries' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 1, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro5, null, 2, 2, false, false, new ArrayCollection([$italy]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 1, false, false, new ArrayCollection([$austria, $hungary]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $hungary]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$italy]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro5Desc, StockAlignmentReportHandler::WITHOUT_RESTRICTED_COUNTRIES, 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $hungaryDesc, $italyDesc]), 1],
                    [$euro6Desc, StockAlignmentReportHandler::WITHOUT_RESTRICTED_COUNTRIES, 1],
                ],
            ],
            'mark all candidates as requiring a permit for Hungary' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, false, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 2],
                    [$euro5Desc, implode(',', [$hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 2],
                    [$euro6Desc, implode(',', [$austriaDesc, $hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc]), 1],
                ],
            ],
            'sort the output' => [
                'ranges' => [
                    IrhpPermitRange::create(null, $euro5, null, 1, 100, false, false, new ArrayCollection([$greece, $hungary, $italy, $russia]), null, null),
                    IrhpPermitRange::create(null, $euro6, null, 1, 100, false, false, new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]), null, null),
                ],
                'candidatePermits' => [
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro5
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$austria, $greece, $hungary, $italy, $russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                    IrhpCandidatePermit::createNew(
                        IrhpPermitApplication::createNewForIrhpApplication(
                            m::mock(IrhpApplication::class)->shouldReceive('getCountrys')->andReturn(new ArrayCollection([$russia]))->getMock(),
                            m::mock(IrhpPermitWindow::class)
                        ),
                        $euro6
                    ),
                ],
                'expected' => [
                    [$euro5Desc, implode(',', [$greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro5Desc, implode(',', [$hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $greeceDesc, $hungaryDesc, $italyDesc, $russiaDesc]), 1],
                    [$euro6Desc, implode(',', [$austriaDesc, $hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc]), 1],
                    [$euro6Desc, implode(',', [$hungaryDesc, $russiaDesc]), 1],
                ],
            ],
        ];
    }
}
