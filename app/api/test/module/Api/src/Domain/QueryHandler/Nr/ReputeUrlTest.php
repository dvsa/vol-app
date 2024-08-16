<?php

/**
 * Repute Url test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Nr;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Nr\ReputeUrl;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification as TmQualificationEntity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\TransportManager as TransportManagerRepo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TmEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Transfer\Query\Nr\ReputeUrl as Qry;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;

/**
 * Repute Url test
 */
class ReputeUrlTest extends QueryHandlerTestCase
{
    protected $nationalRegisterConfig = [
        'nr' => [
            'repute_url' => [
                'uri' => 'https://repute-url.com'
            ]
        ]
    ];

    public function setUp(): void
    {
        $this->sut = new ReputeUrl();
        $this->mockRepo('TransportManager', TransportManagerRepo::class);

        $this->mockedSmServices = [
            'config' => $this->nationalRegisterConfig,
        ];

        parent::setUp();
    }

    /**
     * Tests correct response is returned when not all repute check data is present
     */
    public function testHandleQueryMissingData()
    {
        $tmId = 111;

        $query = Qry::create(['id' => $tmId]);

        $tm = m::mock(TmEntity::class)->makePartial();
        $tm->shouldReceive('hasReputeCheckData')->once()->andReturn(false);

        $this->repoMap['TransportManager']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($tm);

        $this->assertEquals(['reputeUrl' => null], $this->sut->handleQuery($query));
    }

    /**
     * @dataProvider handleQueryProvider
     *
     * @param string $serialNo
     * @param string $querySerialNo
     * @param string $countryCode
     * @param string $queryCountryCode
     */
    public function testHandleQuery($serialNo, $querySerialNo, $countryCode, $queryCountryCode)
    {
        $tmId = 111;
        $tmForename = 'forename';
        $tmFamilyName = 'family name';
        $tmBirthPlace = 'birth place';
        $tmBirthDate = '2015-12-25';
        $qualIssuedDate = '2013-04-01';

        $qualification = m::mock(TmQualificationEntity::class)->makePartial();
        $qualification->shouldReceive('getCountryCode->getId')->andReturn($countryCode);
        $qualification->shouldReceive('getSerialNo')->andReturn($serialNo);
        $qualification->shouldReceive('getIssuedDate')->andReturn($qualIssuedDate);
        $mostRecentQual = new ArrayCollection([$qualification]);

        $person = m::mock(PersonEntity::class);
        $person->shouldReceive('getForename')->andReturn($tmForename);
        $person->shouldReceive('getFamilyName')->andReturn($tmFamilyName);
        $person->shouldReceive('getBirthPlace')->andReturn($tmBirthPlace);
        $person->shouldReceive('getBirthDate')->andReturn($tmBirthDate);

        $query = Qry::create(['id' => $tmId]);

        $tm = m::mock(TmEntity::class)->makePartial();
        $tm->shouldReceive('hasReputeCheckData')->once()->andReturn(true);
        $tm->shouldReceive('getMostRecentQualification')->once()->andReturn($mostRecentQual);
        $tm->shouldReceive('getHomeCd->getPerson')->once()->andReturn($person);

        $this->repoMap['TransportManager']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($tm);

        $expectedQueryVars = [
            'CA' => ReputeUrl::FIELD_CA,
            'GivenName' => $tmForename,
            'FamilyName' => $tmFamilyName,
            'DateOfBirth' => date(ReputeUrl::DATE_FORMAT, strtotime($tmBirthDate)),
            'PlaceOfBirth' => $tmBirthPlace,
            'CPCNo' => $querySerialNo,
            'CPCIssueDate' => date(ReputeUrl::DATE_FORMAT, strtotime($qualIssuedDate)),
            'CPCCountry' => $queryCountryCode,
            'Target' => ReputeUrl::FIELD_TARGET
        ];

        $baseUrl = $this->nationalRegisterConfig['nr']['repute_url']['uri'];
        $expectedResult = ['reputeUrl' => $baseUrl . http_build_query($expectedQueryVars)];

        $this->assertEquals($expectedResult, $this->sut->handleQuery($query));
    }

    /**
     * provider for testHandleQuery
     *
     * @return array
     */
    public function handleQueryProvider()
    {
        return [
            [null, ReputeUrl::FIELD_QUAL_UNKNOWN,'GB', 'UK'],
            ['serialNo', 'serialNo', 'GB', 'UK']
        ];
    }
}
