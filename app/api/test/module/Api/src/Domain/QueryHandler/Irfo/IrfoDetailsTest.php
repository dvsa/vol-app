<?php

declare(strict_types=1);

/**
 * IrfoDetails Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Irfo;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Irfo\IrfoDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoDetails as Qry;
use Mockery as m;

/**
 * IrfoDetails Test
 */
final class IrfoDetailsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new IrfoDetails();
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Organisation']->shouldReceive('fetchIrfoDetailsUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }

    public function testHandleQueryBundleKeepsTradingNamesNotLinkedToALicenceWhenTheTradingNameListIsLoaded(): void
    {
        $query = Qry::create(['id' => 1]);

        $irfoOnly = new TradingName('Irfo only');
        $linked = new TradingName('Linked');
        $linked->setLicence(m::mock(Licence::class));

        $kept = null;
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->shouldReceive('serialize')
            ->once()
            ->andReturnUsing(
                function (array $bundle) use ($irfoOnly, $linked, &$kept) {
                    $kept = (new ArrayCollection([$linked, $irfoOnly]))
                        ->matching($bundle['tradingNames']['criteria'])
                        ->getValues();

                    return [];
                }
            );

        $this->repoMap['Organisation']->shouldReceive('fetchIrfoDetailsUsingId')
            ->with($query)
            ->andReturn($organisation);

        $this->sut->handleQuery($query)->serialize();

        $this->assertSame([$irfoOnly], $kept);
    }
}
