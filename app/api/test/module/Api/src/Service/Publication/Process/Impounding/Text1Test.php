<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Impounding;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson as OrganisationPersonEntity;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as TradingNameEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text1;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text1Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Text1Test extends MockeryTestCase
{
    /**
     *
     * @param $organisationType
     * @param $personPrefix
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the hearing text1 filter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('processTestProvider')]
    public function testProcess(mixed $organisationType, mixed $personPrefix): void
    {

        $sut = new Text1();

        $impoundingId = 99;
        $licenceAddress = 'line 1, line 2, line 3, line 4, town, postcode';
        $licNo = 'OB1234567';
        $venueOther = 'Venue Information';
        $hearingDate = '12 May 2014';
        $hearingTime = '14:30';
        $organisationName = 'Organisation Name';
        $organisationTradingName = 'Organisation Trading Name';
        $organisationTradingName2 = 'Organisation Trading Name 2';
        $personForename = 'John';
        $personFamilyName = 'Smith';

        $personName = $personForename . ' ' . $personFamilyName;

        $tradingName = m::mock(TradingNameEntity::class);
        $tradingName->shouldReceive('getId')
            ->andReturn(5);
        $tradingName->shouldReceive('getName')
            ->andReturn($organisationTradingName);

        $tradingName2 = m::mock(TradingNameEntity::class);
        $tradingName2->shouldReceive('getId')->andReturn(6);
        $tradingName2->shouldReceive('getName')->andReturn($organisationTradingName2);
        $tradingNames = new ArrayCollection([$tradingName, $tradingName2]);

        $personMock = m::mock(PersonEntity::class);
        $personMock->shouldReceive('getForename')->andReturn($personForename);
        $personMock->shouldReceive('getFamilyName')->andReturn($personFamilyName);
        $personMock->shouldReceive('getFullName')->andReturn($personForename . ' ' . $personFamilyName);

        $organisationPerson = m::mock(OrganisationPersonEntity::class);
        $organisationPerson->shouldReceive('getPerson')->andReturn($personMock);
        $organisationPersons = new ArrayCollection([$organisationPerson]);

        $organisationMock = m::mock(OrganisationEntity::class);
        $organisationMock->shouldReceive('getName')->andReturn($organisationName);
        $organisationMock->shouldReceive('getTradingNames')->andReturn($tradingNames);
        $organisationMock->shouldReceive('getOrganisationPersons')->andReturn($organisationPersons);
        $organisationMock->shouldReceive('getType->getId')->andReturn($organisationType);
        $organisationMock->shouldReceive('isSoleTrader')->andReturn(false);

        $licenceMock = m::mock(LicenceEntity::class)->makePartial();
        $licenceMock->shouldReceive('getOrganisation')->andReturn($organisationMock);
        $licenceMock->shouldReceive('getLicNo')->andReturn($licNo);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getLicence')->andReturn($licenceMock);
        $publicationLink->shouldReceive('getLicence->getOrganisation')->andReturn($organisationMock);
        $publicationLink->shouldReceive('getImpounding->getId')->andReturn($impoundingId);

        $input = [
            'licenceAddress' => $licenceAddress,
            'venueOther' => $venueOther,
            'formattedHearingDate' => $hearingDate,
            'formattedHearingTime' => $hearingTime,
            'licenceNo' => $licNo,
            'licencePeople' => [$personMock]
        ];

        $expectedString = sprintf(
            'Impounding hearing (%s) to be held at %s, on %s commencing at %s'
            . "\n" . '%s'
            . "\n" . '%s T/A %s'
            . "\n" . '%s'
            . "\n" . '%s',
            $impoundingId,
            $venueOther,
            $hearingDate,
            $hearingTime,
            $licNo,
            $organisationName,
            $organisationTradingName,
            $personPrefix . $personName,
            $licenceAddress
        );

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText1());
    }

    /**
     * Filter provider
     *
     * @return array
     */
    public static function processTestProvider(): array
    {
        return [
            [OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY, 'Director(s): ']
        ];
    }
}
