<?php

declare(strict_types=1);

/**
 * Financial Evidence Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\FinancialEvidence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Application\FinancialEvidence as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Financial Evidence Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialEvidenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FinancialEvidence();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockedSmServices['FinancialStandingHelperService'] = m::mock(FinancialStandingHelperService::class);
        $mockedAuth = m::mock(AuthorizationService::class)->makePartial();
        $this->mockedSmServices[AuthorizationService::class] = $mockedAuth;

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicationId = 111;
        $applicationLicenceId = 7;
        $goodsOrPsv = Licence::LICENCE_CATEGORY_GOODS_VEHICLE;
        $totAuthVehicles = 3;
        $organisationLicences = $this->getMockOrganisationLicences();
        $otherNewApplications = $this->getOtherNewApplications();
        $totalRequired = 30400;

        $query = Qry::create(['id' => $applicationId]);

        $mockGoodsOrPsv = m::mock()
            ->shouldReceive('getId')
            ->andReturn($goodsOrPsv)
            ->getMock();

        $mockDocument = m::mock()
            ->shouldReceive('serialize')
            ->with([])
            ->once()
            ->andReturn(['doc' => 'bar'])
            ->getMock();

        $mockApplication = m::mock(Application::class)
            ->shouldReceive('getApplicationDocuments')
            ->with('category', 'subCategory')
            ->andReturn([$mockDocument])
            ->once()
            ->shouldReceive('serialize')
            ->andReturn(['id' => $applicationId])
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn($totAuthVehicles)
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn($mockGoodsOrPsv)
            ->shouldReceive('getOtherActiveLicencesForOrganisation')
            ->andReturn(
                array_filter(
                    $organisationLicences,
                    fn($licence) => $licence->getId() !== $applicationLicenceId
                )
            )
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($mockApplication)
            ->once()
            ->shouldReceive('getCategoryReference')
            ->with(Category::CATEGORY_APPLICATION)
            ->andReturn('category')
            ->once()
            ->shouldReceive('getSubCategoryReference')
            ->with(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL)
            ->andReturn('subCategory')
            ->once();

        $this->mockedSmServices['FinancialStandingHelperService']
            ->shouldReceive('getRatesForView')
            ->withNoArgs()
            ->andReturn(
                [
                    'restrictedHeavyGoodsFirst' => 3100,
                    'restrictedHeavyGoodsAdditional' => 1700,
                    'restrictedPassengerServiceFirst' => 3100,
                    'restrictedPassengerServiceAdditional' => 1700,
                    'standardNationalHeavyGoodsFirst' => 8000,
                    'standardNationalHeavyGoodsAdditional' => 4500,
                    'standardNationalPassengerServiceFirst' => 8000,
                    'standardNationalPassengerServiceAdditional' => 4500,
                    'standardInternationalHeavyGoodsFirst' => 8000,
                    'standardInternationalHeavyGoodsAdditional' => 4500,
                    'standardInternationalLightGoodsFirst' => 1600,
                    'standardInternationalLightGoodsAdditional' => 800,
                    'standardInternationalPassengerServiceFirst' => 8000,
                    'standardInternationalPassengerServiceAdditional' => 4500,
                ]
            );
        $this->mockedSmServices['FinancialStandingHelperService']
            ->shouldReceive('getOtherNewApplications')
            ->with($mockApplication)
            ->andReturn($otherNewApplications);
        $this->mockedSmServices['FinancialStandingHelperService']
            ->shouldReceive('getRequiredFinance')
            ->with($mockApplication)
            ->andReturn($totalRequired);
        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn(new ArrayCollection([]))->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->andReturn($mockedId);

        $expectedResult = [
            'id' => $applicationId,
            'documents' => [['doc' => 'bar']],
            'financialEvidence' => [
                'requiredFinance' => $totalRequired,
                'restrictedHeavyGoodsFirst' => 3100,
                'restrictedHeavyGoodsAdditional' => 1700,
                'restrictedPassengerServiceFirst' => 3100,
                'restrictedPassengerServiceAdditional' => 1700,
                'standardNationalHeavyGoodsFirst' => 8000,
                'standardNationalHeavyGoodsAdditional' => 4500,
                'standardNationalPassengerServiceFirst' => 8000,
                'standardNationalPassengerServiceAdditional' => 4500,
                'standardInternationalHeavyGoodsFirst' => 8000,
                'standardInternationalHeavyGoodsAdditional' => 4500,
                'standardInternationalLightGoodsFirst' => 1600,
                'standardInternationalLightGoodsAdditional' => 800,
                'standardInternationalPassengerServiceFirst' => 8000,
                'standardInternationalPassengerServiceAdditional' => 4500,
                'applicationVehicles' => 3,
                'otherLicenceVehicles' => 4,
                'otherApplicationVehicles' => 2,
            ]
        ];

        $this->assertEquals($expectedResult, $this->sut->handleQuery($query)->serialize());
    }

    protected function getMockOrganisationLicences(): array
    {
        $values = [
            // id, category, vehicle auth
            [7, 'lcat_gv', 3], // current app licence, should be ignored
            [8, 'lcat_gv', 3],
            [9, 'lcat_psv', 1],
        ];

        return array_map(
            function ($value) {
                $mockLicence = m::mock();
                $mockLicence
                    ->shouldReceive('getId')
                    ->andReturn($value[0])
                    ->shouldReceive('getTotAuthVehicles')
                    ->andReturn($value[2]);

                // can't chain demeter expectations :-/
                $mockLicence
                    ->shouldReceive('getGoodsOrPsv->getId')
                    ->andReturn($value[1]);

                return $mockLicence;
            },
            $values
        );
    }

    protected function getOtherNewApplications(): array
    {
        $values = [
            // vehicle auth
            [2],
        ];

        return array_map(
            function ($value) {
                $mockApplication = m::mock();
                $mockApplication
                    ->shouldReceive('getTotAuthVehicles')
                    ->andReturn($value[0]);

                return $mockApplication;
            },
            $values
        );
    }
}
