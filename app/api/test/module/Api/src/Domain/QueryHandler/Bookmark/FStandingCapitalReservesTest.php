<?php

declare(strict_types=1);

/**
 * FStandingCapitalReserves Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\FStandingCapitalReserves;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\FStandingCapitalReserves as Qry;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;

/**
 * FStandingCapitalReserves Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FStandingCapitalReservesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FStandingCapitalReserves();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);

        $this->mockedSmServices['FinancialStandingHelperService'] = m::mock(FinancialStandingHelperService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;
        $query = Qry::create(
            [
                'organisation' => $organisationId,
            ]
        );

        $this->mockedSmServices['FinancialStandingHelperService']
            ->shouldReceive('getFinanceCalculationForOrganisation')->with(69)->once()->andReturn('RESULT');

        $this->assertEquals('RESULT', $this->sut->handleQuery($query));
    }
}
