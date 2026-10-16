<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Api\Service\Idp\ApplicantProfileBuilder;
use PHPUnit\Framework\TestCase;
use Mockery as m;

final class ApplicantProfileBuilderTest extends TestCase
{
    private m\MockInterface $helper;
    private ApplicantProfileBuilder $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->helper = m::mock(FinancialStandingHelperService::class);
        $this->sut = new ApplicantProfileBuilder($this->helper);
    }

    public function testBuildsTheFullApplicantProfile(): void
    {
        $application = $this->givenApplication(
            tradingNames: ['Speedy Freight'],
            people: [['Mr', 'John', 'Smith'], ['Ms', 'Jane', 'Doe']],
            requiredFinance: 26000,
        );

        $this->assertSame(
            [
                'organisation_name' => 'Test Haulage Ltd',
                'licence_number' => 'OB2014165',
                'nature_of_business' => 'Marketing',
                'business_type' => 'Registered Company',
                'people' => [
                    'Mr John Smith',
                    'Ms Jane Doe',
                ],
                'application_number' => 1056017,
                'trading_name' => 'Speedy Freight',
                'required_funds' => 26000,
                'licence_type' => 'Standard National',
                'application_date' => '2018-01-08',
                'vehicles_requested' => 0,
            ],
            $this->sut->build($application)
        );
    }

    public function testTradingNameIsNoneStringWhenTheLicenceHasNone(): void
    {
        $application = $this->givenApplication(tradingNames: []);
        $this->assertSame('None', $this->sut->build($application)['trading_name']);
    }

    public function testMultipleTradingNamesAreJoinedAndDeduplicated(): void
    {
        $application = $this->givenApplication(tradingNames: ['Alpha', 'Beta', 'Alpha', '  ']);
        $this->assertSame('Alpha, Beta', $this->sut->build($application)['trading_name']);
    }

    public function testRequiredFundsIsReturnedDirectlyFromHelper(): void
    {
        $application = $this->givenApplication(requiredFinance: 94600);
        $this->assertSame(94600, $this->sut->build($application)['required_funds']);
    }

    public function testFallsBackToRefDataIdWhenDescriptionIsEmpty(): void
    {
        $application = $this->givenApplication(businessTypeDescription: '');
        $this->assertSame('org_t_rc', $this->sut->build($application)['business_type']);
    }

    public function testPeopleIsEmptyWhenOrganisationHasNone(): void
    {
        $application = $this->givenApplication(people: []);
        $this->assertSame([], $this->sut->build($application)['people']);
    }

    public function testPeopleSkipsPersonWithNoNameParts(): void
    {
        $application = $this->givenApplication(people: [['', '', '']]);
        $this->assertSame([], $this->sut->build($application)['people']);
    }

    /**
     * @param string[] $tradingNames
     * @param array<int, array{0: string, 1: string, 2: string}> $people
     */
    private function givenApplication(
        array $tradingNames = [],
        array $people = [],
        float|int $requiredFinance = 0,
        string $businessTypeDescription = 'Registered Company',
    ): m\MockInterface {
        $orgPersons = new ArrayCollection(array_map(function (array $p): m\MockInterface {
            [$title, $forename, $familyName] = $p;

            $titleRefData = $title !== '' ? $this->refData('title_mr', $title) : null;

            $person = m::mock(Person::class);
            $person->shouldReceive('getTitle')->andReturn($titleRefData);
            $person->shouldReceive('getForename')->andReturn($forename);
            $person->shouldReceive('getFamilyName')->andReturn($familyName);

            $orgPerson = m::mock(OrganisationPerson::class);
            $orgPerson->shouldReceive('getPerson')->andReturn($person);

            return $orgPerson;
        }, $people));

        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getName')->andReturn('Test Haulage Ltd');
        $organisation->shouldReceive('getNatureOfBusiness')->andReturn('Marketing');
        $organisation->shouldReceive('getType')->andReturn(
            $this->refData('org_t_rc', $businessTypeDescription)
        );
        $organisation->shouldReceive('getOrganisationPersons')->andReturn($orgPersons);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getLicNo')->andReturn('OB2014165');
        $licence->shouldReceive('getTradingNames')->andReturn(
            new ArrayCollection(array_map(function (string $name): m\MockInterface {
                $tradingName = m::mock(TradingName::class);
                $tradingName->shouldReceive('getName')->andReturn($name);
                return $tradingName;
            }, $tradingNames))
        );

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('getLicenceType')->andReturn(
            $this->refData('ltyp_sn', 'Standard National')
        );
        $application->shouldReceive('getId')->andReturn(1056017);
        $application->shouldReceive('getApplicationDate')->withNoArgs()->andReturn('2018-01-08');
        $application->shouldReceive('getTotAuthVehicles')->andReturn(0);
        $application->shouldReceive('getOtherActiveLicencesForOrganisation')->andReturn([]);
        $this->helper->shouldReceive('getOtherNewApplications')->with($application)->andReturn([]);
        $this->helper->shouldReceive('getRequiredFinance')->with($application)->andReturn($requiredFinance);

        return $application;
    }

    private function refData(string $id, string $description): m\MockInterface
    {
        $refData = m::mock(RefData::class);
        $refData->shouldReceive('getId')->andReturn($id);
        $refData->shouldReceive('getDescription')->andReturn($description);

        return $refData;
    }
}
